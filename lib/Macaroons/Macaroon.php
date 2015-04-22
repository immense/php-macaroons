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
    return strtolower( Utils::hex( $this->signature ) );
  }

  public function addFirstPartyCaveat($predicate)
  {
    $this->caveats[] = new Caveat($predicate);
    $this->signature = Utils::signFirstPartyCaveat($this->signature, $predicate);
  }

  public function addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation)
  {
    $derivedCaveatKey = Utils::truncateOrPad( Utils::generateDerivedKey($caveatKey) );
    $truncatedOrPaddedSignature = Utils::truncateOrPad( $this->signature );
    // Generate cipher using libsodium
    $nonce = \Sodium::randombytes_buf(\Sodium::CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = \Sodium::crypto_secretbox($truncatedOrPaddedSignature, $nonce, $derivedCaveatKey);
    $verificationId = base64_encode($ciphertext);
    $this->caveats[] = new Caveat($caveatId, $verificationId, $caveatLocation);
    $this->signature = Utils::signThirdPartyCaveat($this->signature, $verificationId, $caveatId);
  }

  private function initialSignature($key, $identifier)
  {
    return Utils::hmac( Utils::generateDerivedKey($key), $identifier);
  }
}
