<?php

namespace Macaroons\Serializers;

use Macaroons\Macaroon;

class BaseSerializer
{
  protected $macaroon;
  public function __construct(Macaroon $macaroon = NULL)
  {
    $this->macaroon = $macaroon;
  }
}
