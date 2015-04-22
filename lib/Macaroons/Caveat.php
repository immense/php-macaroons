<?php

namespace Macaroons;

class Caveat
{
  private $caveat_id;
  private $location;
  private $verification_id;

  public function __construct($caveat_id, $verification_id = NULL, $location = NULL)
  {
    $this->id = $caveat_id;
    $this->verification_id = $verification_id;
    $this->location = $location;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getLocation()
  {
    return $this->location;
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
