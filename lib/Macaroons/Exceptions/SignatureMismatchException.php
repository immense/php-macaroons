<?php

namespace Macaroons\Exceptions;

/**
 * Exception raised by the verifier when two Macaroons have mismatching
 * signatures
 *
 * This class was created to be able to catch this type of exception.
 *
 * Catching this exception is very useful when testing
 */
class SignatureMismatchException extends \Exception
{
}
