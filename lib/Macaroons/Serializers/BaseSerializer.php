<?php

namespace Macaroons\Serializers;

use Macaroons\Macaroon;

/**
 * Base serializer class to handle setup
 */
abstract class BaseSerializer
{
  /**
   * Macaroon to be serialized
   * @var Macaroon
   */
  protected $macaroon;

  /**
   * Default constructor that initializes the macaroon property
   * @param Macaroon|null $macaroon [description]
   */
  public function __construct(Macaroon $macaroon = NULL)
  {
    $this->macaroon = $macaroon;
  }
}
