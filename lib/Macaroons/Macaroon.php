<?php

namespace Macaroons;

class Macaroon
{
  private $id;
  private $location;
  private $signature;
  private $caveats = array();

  public function __construct($key, $identifier, $location)
  {
    $this->identifier = $identifier;
    $this->location = $location;
    $this->signature = $this->initialSignature($key, $identifier);
  }

  public function getIdentifier()
  {
    return $this->identifier;
  }

  public function getLocation()
  {
    return $this->location;
  }

  public function getSignature()
  {
    return strtolower( Utils::hexlify( $this->signature ) );
  }

  public function getFirstPartyCaveats()
  {
    return array_filter($this->caveats, function(Caveat $caveat){
      return $caveat->getIsFirstParty();
    });
  }

  public function getThirdPartyCaveats()
  {
    return array_filter($this->caveats, function(Caveat $caveat){
      return $caveat->getIsThirdParty();
    });
  }

  public function getCaveats()
  {
    return $this->caveats;
  }

  public function setSignature($signature)
  {
    if (!isset($signature))
      throw new \InvalidArgumentException('Must supply updated signature');
    $this->signature = $signature;
  }

  public function setCaveats(Array $caveats)
  {
    $this->caveats = $caveats;
  }

  public function addFirstPartyCaveat($predicate)
  {
    array_push($this->caveats, new Caveat($predicate));
    $this->signature = Utils::signFirstPartyCaveat($this->signature, $predicate);
  }

  public function addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation)
  {
    $derivedCaveatKey = Utils::truncateOrPad( Utils::generateDerivedKey($caveatKey) );
    $truncatedOrPaddedSignature = Utils::truncateOrPad( $this->signature );
    // Generate cipher using libsodium
    $nonce = \Sodium::randombytes_buf(\Sodium::CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = \Sodium::crypto_secretbox($truncatedOrPaddedSignature, $nonce, $derivedCaveatKey);
    $verificationId = Utils::base64_strict_encode($ciphertext);
    array_push($this->caveats, new Caveat($caveatId, $verificationId, $caveatLocation));
    $this->signature = Utils::signThirdPartyCaveat($this->signature, $verificationId, $caveatId);
  }

  /**
   * [prepareForRequest description]
   * @param  Macaroon $macaroon
   * @return Macaroon           bound Macaroon (protected discharge)
   */
  public function prepareForRequest(Macaroon $macaroon)
  {
    $boundMacaroon = clone $macaroon;
    $boundMacaroon->setSignature($this->bindSignature($macaroon->getSignature()));
    return $boundMacaroon;
  }

  /**
   * [bindSignature description]
   * @param  string $signature
   * @return string
   */
  public function bindSignature($signature)
  {
    $key                  = Utils::truncateOrPad("\0");
    $currentSignatureHash = Utils::hmac($key, Utils::unhexlify($this->getSignature()));
    $newSignatureHash     = Utils::hmac($key, Utils::unhexlify($signature));
    return Utils::hmac($key, $currentSignatureHash . $newSignatureHash);
  }

  private function initialSignature($key, $identifier)
  {
    return Utils::hmac( Utils::generateDerivedKey($key), $identifier);
  }

  // TODO: Move these into a separate object
  public function serialize()
  {
    $p = new Packet();
    $s = $p->packetize(
                        array(
                          'location' => $this->location,
                          'identifier' => $this->identifier
                        )
                      );
    foreach ($this->caveats as $caveat)
    {
      $p = new Packet();
      $s = $s . $p->packetize(
                                array(
                                  'vid' => $caveat->getVerificationId(),
                                  'cl' => $caveat->getCaveatLocation()
                                )
                              );
    }
    $p = new Packet();
    $s = $s . $p->packetize(array('signature' => $this->signature));
    return Utils::base64_url_encode($s);
  }

  public static function deserialize($serialized)
  {
    $location   = NULL;
    $identifier = NULL;
    $signature  = NULL;
    $caveats    = array();
    $decoded    = Utils::base64_url_decode($serialized);
    $index      = 0;

    while ($index < strlen($decoded))
    {
      // TOOD: Replace 4 with PACKET_PREFIX_LENGTH
      $packetLength    = hexdec(substr($decoded, $index, 4));
      $packetDataStart = $index + 4;
      $strippedPacket  = substr($decoded, $packetDataStart, strpos($decoded, "\n", $index) - $packetDataStart);
      $packet          = new Packet();
      $packet          = $packet->decode($strippedPacket);

      switch($packet->getKey())
      {
        case 'location':
          $location = $packet->getData();
        break;
        case 'identifier':
          $identifier = $packet->getData();
        break;
        case 'signature':
          $signature = $packet->getData();
        break;
        case 'cid':
          $caveat = new Caveat($packet->getData());
        break;
        case 'vid':
          $caveat = $caveats[ count($caveats) - 1 ];
          $caveat->setVerificationId($packet->getData());
        break;
        case 'cl':
          $caveat = $caveats[ count($caveats) - 1 ];
          $caveat->setCaveatLocation($packet->getData());
        break;
        default:
          throw new \DomainException('Invalid key in binary macaroon. Macaroon may be corrupted.');
        break;
      }
      $index = $index + $packetLength;
    }
    $m = new Macaroon('no_key', $identifier, $location);
    $m->setCaveats($caveats);
    $m->setSignature($signature);
    return $m;
  }
}
