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

  private function initialSignature($key, $identifier)
  {
    return Utils::hmac( Utils::generateDerivedKey($key), $identifier);
  }
}
