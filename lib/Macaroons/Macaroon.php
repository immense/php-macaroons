<?php

namespace Macaroons;

use Macaroons\Exceptions\InvalidMacaroonKeyException;
use Macaroons\Serializers\BinarySerializer;

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
      return $caveat->isFirstParty();
    });
  }

  public function getThirdPartyCaveats()
  {
    return array_filter($this->caveats, function(Caveat $caveat){
      return $caveat->isThirdParty();
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
    $nonce = \Sodium\randombytes_buf(\Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
    $verificationId = $nonce . \Sodium\crypto_secretbox($derivedCaveatKey, $nonce, $truncatedOrPaddedSignature);
    array_push($this->caveats, new Caveat($caveatId, $verificationId, $caveatLocation));
    $this->signature = Utils::signThirdPartyCaveat($this->signature, $verificationId, $caveatId);
    \Sodium\memzero($caveatKey);
    \Sodium\memzero($derivedCaveatKey);
    \Sodium\memzero($caveatId);
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

  public function inspect()
  {
    $str = "location {$this->location}\n";
    $str .= "identifier {$this->identifier}\n";
    foreach ($this->caveats as $caveat)
    {
      $str .= "$caveat\n";
    }
    $str .= "signature {$this->getSignature()}";
    return $str;
  }

  private function initialSignature($key, $identifier)
  {
    return Utils::hmac( Utils::generateDerivedKey($key), $identifier);
  }

  public function serialize()
  {
    return (new BinarySerializer($this))->serialize();
  }

  public static function deserialize($serialized)
  {
    return (new BinarySerializer())->deserialize($serialized);
  }

  public function toJSON()
  {
    return json_encode(array(
      'location' => $this->location,
      'identifier' => $this->identifier,
      'caveats' => array_map(function(Caveat $caveat){
        $caveatAsArray = $caveat->toArray();
        if ($caveat->isThirdParty())
          $caveatAsArray['vid'] = Utils::hexlify($caveatAsArray['vid']);
        return $caveatAsArray;
      }, $this->getCaveats()),
      'signature' => $this->getSignature()
    ));
  }

  public static function fromJSON($serialized)
  {
    $data       = json_decode($serialized);
    $location   = $data->location;
    $identifier = $data->identifier;
    $signature  = $data->signature;
    $m          = new Macaroon(
                                'no_key',
                                $identifier,
                                $location
                              );
    $caveats = array_map(function(stdClass $data){
      $caveatId       = $data->cid;
      $verificationId = $data->vid;
      $caveatLocation = $data->cl;
      return new Caveat($caveatId, $verificationId, $caveatLocation);
    }, $data->caveats);
    $m->setCaveats($caveats);
    $m->setSignature(Utils::unhexlify($signature));
    return $m;
  }
}
