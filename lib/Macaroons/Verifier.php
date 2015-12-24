<?php

namespace Macaroons;

use Macaroons\Exceptions\SignatureMismatchException;
use Macaroons\Exceptions\CaveatUnsatisfiedException;

class Verifier
{
  private $predicates = array();
  private $callbacks = array();
  private $calculatedSignature;

  /**
   * return predicates to verify
   * @return Array|array
   */
  public function getPredicates()
  {
    return $this->predicates;
  }

  /**
   * returns verifier callbacks
   * @return Array|array
   */
  public function getCallbacks()
  {
    return $this->callbacks;
  }

  /**
   * sets array of predicates
   * @param Array $predicates
   */
  public function setPredicates(Array $predicates)
  {
    $this->predicates = $predicates;
  }

  /**
   * set array of callbacks
   * @param Array $callbacks
   */
  public function setCallbacks(Array $callbacks)
  {
    $this->callbacks = $callbacks;
  }

  /**
   * adds a predicate to the verifier
   * @param string
   */
  public function satisfyExact($predicate)
  {
    if (!isset($predicate))
      throw new \InvalidArgumentException('Must provide predicate');
    array_push($this->predicates, $predicate);
  }

  /**
   * adds a callback to array of callbacks
   * $callback can be anything that is callable including objects
   * that implement __invoke
   * See http://php.net/manual/en/language.types.callable.php for more details
   * @param function|object|array
   */
  public function satisfyGeneral($callback)
  {
    if (!isset($callback))
      throw new \InvalidArgumentException('Must provide a callback function');
    if (!is_callable($callback))
      throw new \InvalidArgumentException('Callback must be a function');
    array_push($this->callbacks, $callback);
  }

  /**
   * [verify description]
   * @param  Macaroon $macaroon
   * @param  string   $key
   * @param  Array    $dischargeMacaroons
   * @return boolean
   */
  public function verify(Macaroon $macaroon, $key, Array $dischargeMacaroons = array())
  {
    $key = Utils::generateDerivedKey($key);
    return $this->verifyDischarge(
                                  $macaroon,
                                  $macaroon,
                                  $key,
                                  $dischargeMacaroons
                                  );
  }

  /**
   * [verifyDischarge description]
   * @param  Macaroon    $rootMacaroon
   * @param  Macaroon    $macaroon
   * @param  string      $key
   * @param  Array|array $dischargeMacaroons
   * @return boolean|throws SignatureMismatchException
   */
  public function verifyDischarge(Macaroon $rootMacaroon, Macaroon $macaroon, $key, Array $dischargeMacaroons = array())
  {
    $this->calculatedSignature = Utils::hmac($key, $macaroon->getIdentifier());
    $this->verifyCaveats($macaroon, $dischargeMacaroons);

    if ($rootMacaroon != $macaroon)
    {
      $this->calculatedSignature = $rootMacaroon->bindSignature(strtolower(Utils::hexlify($this->calculatedSignature)));
    }

    $signature = Utils::unhexlify($macaroon->getSignature());
    if ($this->signaturesMatch($this->calculatedSignature, $signature) === FALSE)
    {
      throw new SignatureMismatchException('Signatures do not match.');
    }
    return true;
  }

  /**
   * verifies all first and third party caveats of macaroon are valid
   * @param  Macaroon
   * @param  Array
   */
  private function verifyCaveats(Macaroon $macaroon, Array $dischargeMacaroons = array())
  {
    foreach ($macaroon->getCaveats() as $caveat)
    {
      $caveatMet = false;
      if ($caveat->isFirstParty())
        $caveatMet = $this->verifyFirstPartyCaveat($caveat);
      else if ($caveat->isThirdParty())
        $caveatMet = $this->verifyThirdPartyCaveat($caveat, $macaroon, $dischargeMacaroons);
      if (!$caveatMet)
        throw new CaveatUnsatisfiedException("Caveat not met. Unable to satisfy: {$caveat->getCaveatId()}");
    }
  }

  private function verifyFirstPartyCaveat(Caveat $caveat)
  {
    $caveatMet = false;
    if (in_array($caveat->getCaveatId(), $this->predicates))
      $caveatMet = true;
    else
    {
      foreach ($this->callbacks as $callback)
      {
        if ($callback($caveat->getCaveatId()))
          $caveatMet = true;
      }
    }
    if ($caveatMet)
      $this->calculatedSignature = Utils::signFirstPartyCaveat($this->calculatedSignature, $caveat->getCaveatId());

    return $caveatMet;
  }

  private function verifyThirdPartyCaveat(Caveat $caveat, Macaroon $rootMacaroon, Array $dischargeMacaroons)
  {
    $caveatMet = false;

    $dischargesMatchingCaveat = array_filter($dischargeMacaroons, function($discharge) use ($rootMacaroon, $caveat) {
      return $discharge->getIdentifier() === $caveat->getCaveatId();
    });

    $caveatMacaroon = array_shift($dischargesMatchingCaveat);

    if (!$caveatMacaroon)
      throw new CaveatUnsatisfiedException("Caveat not met. No discharge macaroon found for identifier: {$caveat->getCaveatId()}");

    $caveatKey = $this->extractCaveatKey($this->calculatedSignature, $caveat);
    $caveatMacaroonVerifier = new Verifier();
    $caveatMacaroonVerifier->setPredicates($this->predicates);
    $caveatMacaroonVerifier->setCallbacks($this->callbacks);

    $caveatMet = $caveatMacaroonVerifier->verifyDischarge(
                                                          $rootMacaroon,
                                                          $caveatMacaroon,
                                                          $caveatKey,
                                                          $dischargeMacaroons
                                                          );
    if ($caveatMet)
    {
      $this->calculatedSignature = Utils::signThirdPartyCaveat(
                                                                $this->calculatedSignature,
                                                                $caveat->getVerificationId(),
                                                                $caveat->getCaveatId()
                                                              );
    }

    return $caveatMet;
  }

  /**
   * returns the derived key from the caveat verification id
   * @param  string $signature
   * @param  Caveat $caveat
   * @return string
   */
  private function extractCaveatKey($signature, Caveat $caveat)
  {
    $verificationHash = $caveat->getVerificationId();
    $nonce            = substr($verificationHash, 0, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
    $verificationId   = substr($verificationHash, \Sodium\CRYPTO_SECRETBOX_NONCEBYTES);
    $key              = Utils::truncateOrPad($signature);
    return \Sodium\crypto_secretbox_open($verificationId, $nonce, $key);
  }

  /**
   * compares the calculated signature of a macaroon and the macaroon supplied
   * by the client
   * The user supplied string MUST be the second argument or this will leak
   * the length of the actual signature
   * @param  string $a known signature from our key and macaroon metadata
   * @param  string $b signature from macaroon we are verifying (from the client)
   * @return boolean
   */
  private function signaturesMatch($a, $b)
  {
    $ret = strlen($a) ^ strlen($b);
    $ret |= array_sum(unpack("C*", $a^$b));

    return !$ret;
  }
}
