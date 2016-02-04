<?php

namespace Macaroons;

use Macaroons\Serializers\BinarySerializer;

/**
 * The Macaroon class contains the data for a Macaroon and handles logic
 * for caveats and binding discharges.
 */
class Macaroon
{
  /**
   * Macaroon's identifier
   * @var string
   */
  private $_identifier;

  /**
   * Macaroon's location
   * @var string
   */
  private $_location;

  /**
   * Macaroon's current signature
   * @var string
   */
  private $_signature;

  /**
   * array of first and third party caveats
   * @var array
   */
  private $_caveats = array();

  /**
   * Creates a new Macaroon with specified key, identifier and location
   * @param string $key
   * @param string $identifier
   * @param string $location
   */
  public function __construct($key, $identifier, $location)
  {
    $this->_identifier = $identifier;
    $this->_location = $location;
    $this->_signature = $this->initialSignature($key, $identifier);
  }

  /**
   * identifier getter
   * @return string
   */
  public function getIdentifier()
  {
    return $this->_identifier;
  }

  /**
   * location getter
   * @return string
   */
  public function getLocation()
  {
    return $this->_location;
  }

  /**
   * signature getter
   * @return string
   */
  public function getSignature()
  {
    return strtolower( Utils::hexlify( $this->_signature ) );
  }

  /**
   * caveats getter
   * @return Array|array
   */
  public function getCaveats()
  {
    return $this->_caveats;
  }

  /**
   * signature setter
   * @param string $signature
   */
  public function setSignature($signature)
  {
    if (!isset($signature))
      throw new \InvalidArgumentException('Must supply updated signature');
    $this->_signature = $signature;
  }

  /**
   * caveats setter
   * @param Array|array $caveats
   */
  public function setCaveats(Array $caveats)
  {
    $this->_caveats = $caveats;
  }

  /**
   * return all first party caveats
   * @return Array|array
   */
  public function getFirstPartyCaveats()
  {
    return array_filter($this->_caveats, function(Caveat $caveat){
      return $caveat->isFirstParty();
    });
  }

  /**
   * returns all third party caveats
   * @return Array|array
   */
  public function getThirdPartyCaveats()
  {
    return array_filter($this->_caveats, function(Caveat $caveat){
      return $caveat->isThirdParty();
    });
  }

  /**
   * adds and signs (updates Macaroon's signature) first party caveat
   * @param [type] $predicate [description]
   */
  public function addFirstPartyCaveat($predicate)
  {
    array_push($this->_caveats, new Caveat($predicate));
    $this->_signature = Utils::signFirstPartyCaveat($this->_signature, $predicate);
  }

  /**
   * adds and signs (updates Macaroon's signature) third party caveat
   * @param string $caveatKey
   * @param string $caveatId
   * @param string $caveatLocation
   */
  public function addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation)
  {
    $derivedCaveatKey = Utils::truncateOrPad( Utils::generateDerivedKey($caveatKey) );
    $truncatedOrPaddedSignature = Utils::truncateOrPad( $this->_signature );
    // Generate cipher using libsodium
    $nonce = \Sodium\randombytes_buf(\Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
    $verificationId = $nonce . \Sodium\crypto_secretbox($derivedCaveatKey, $nonce, $truncatedOrPaddedSignature);
    array_push($this->_caveats, new Caveat($caveatId, $verificationId, $caveatLocation));
    $this->_signature = Utils::signThirdPartyCaveat($this->_signature, $verificationId, $caveatId);
    \Sodium\memzero($caveatKey);
    \Sodium\memzero($derivedCaveatKey);
    \Sodium\memzero($caveatId);
  }

  /**
   * binds a discharge to the current Macaroon
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
   * Generates a new hmac for two signatures.
   * This is used when binding discharges
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

  /**
   * returns a string representation of a Macaroon and all caveats
   * Helpful when debugging implementation and integration issues
   * @return string
   */
  public function inspect()
  {
    $str = "location {$this->_location}\n";
    $str .= "identifier {$this->_identifier}\n";
    foreach ($this->_caveats as $caveat)
    {
      $str .= "$caveat\n";
    }
    $str .= "signature {$this->getSignature()}";
    return $str;
  }

  /**
   * generates the signature of the Macaroon
   * @param  string $key
   * @param  string $identifier
   * @return string
   */
  private function initialSignature($key, $identifier)
  {
    // TODO: This is only used in the constructor
    // move into the constructor and get rid of this method
    return Utils::hmac( Utils::generateDerivedKey($key), $identifier);
  }

  /**
   * return binary serialization
   * @return string
   * @deprecated deprecated since 1.1.0
   */
  public function serialize()
  {
    $serializer = new BinarySerializer($this);
    return $serializer->serialize();
  }

  /**
   * returns a new Macaroon
   * @param  string $serialized
   * @return Macaroon
   * @deprecated deprecated since 1.1.0
   */
  public static function deserialize($serialized)
  {
    $serializer = new BinarySerializer();
    return $serializer->deserialize($serialized);
  }

  /**
   * serializes Macaroon as JSON
   * @return string JSON representation of Macaroon
   * @deprecated deprecated since 1.1.0
   */
  public function toJSON()
  {
    return json_encode(array(
      'location' => $this->_location,
      'identifier' => $this->_identifier,
      'caveats' => array_map(function(Caveat $caveat){
        $caveatAsArray = $caveat->toArray();
        if ($caveat->isThirdParty())
          $caveatAsArray['vid'] = Utils::hexlify($caveatAsArray['vid']);
        return $caveatAsArray;
      }, $this->getCaveats()),
      'signature' => $this->getSignature()
    ));
  }

  /**
   * returns a new Macaroon
   * @param  [type] $serialized [description]
   * @return Macaroon
   * @deprecated deprecated since 1.1.0
   */
  public static function fromJSON($serialized)
  {
    $data       = json_decode($serialized);
    $location   = $data->location;
    $identifier = $data->identifier;
    $signature  = $data->signature;
    $macaroon   = new Macaroon(
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
    $macaroon->setCaveats($caveats);
    $macaroon->setSignature(Utils::unhexlify($signature));
    return $macaroon;
  }
}
