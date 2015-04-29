<?php

namespace Macaroons;

class Caveat
{
  private $caveat_id;
  private $verification_id;
  private $caveat_location;

  public function __construct($caveatId, $verificationId = NULL, $caveatLocation = NULL)
  {
    $this->caveat_id       = $caveatId;
    $this->verification_id = $verificationId;
    $this->caveat_location = $caveatLocation;
  }

  public function getCaveatId()
  {
    return $this->caveat_id;
  }

  public function getCaveatLocation()
  {
    return $this->caveat_location;
  }

  public function getVerificationId()
  {
    return $this->verification_id;
  }

  public function setCaveatLocation($caveatLocation)
  {
    $this->caveat_location = $caveatLocation;
  }

  public function setVerificationId($verificationId)
  {
    $this->verification_id = $verificationId;
  }

  public function isFirstParty()
  {
    return $this->verification_id === NULL;
  }

  public function isThirdParty()
  {
    return !$this->isFirstParty();
  }

}
