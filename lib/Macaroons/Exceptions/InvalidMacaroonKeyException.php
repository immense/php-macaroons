<?php

namespace Macaroons\Exceptions;

/**
 * Exception raised when a binary macaroon contains an invalid key
 *
 * This class was created to be able to catch this type of exception.
 *
 * Catching this exception is very useful when testing
 */
class InvalidMacaroonKeyException extends \Exception
{
}
