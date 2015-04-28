<?php

namespace Macaroons;

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

  public function verifyDischarge(Macaroon $root, Macaroon $macaroon, $key, Array $dischargeMacaroons = array())
  {
    $this->calculatedSignature = Utils::hmac($key, $macaroon->getIdentifier());
    $this->verifyCaveats($macaroon, $dischargeMacaroons);

    if ($root != $macaroon)
    {
      $this->calculatedSignature = $root->bindSignature(strtolower(Utils::hexlify($this->calculatedSignature)));
    }

    $signature = Utils::unhexlify($macaroon->getSignature());
    if ($this->signaturesMatch($this->calculatedSignature, $signature) === FALSE)
    {
      throw new \DomainException('Signatures do not match.');
    }
    return true;
  }

  /**
   * verifies all first and third party caveats of macaroon are valid
   * @param  Macaroon
   * @param  Array
   */
  private function verifyCaveats(Macaroon $macaroon, Array $dischargeMacaroons)
  {
    foreach ($macaroon->getCaveats() as $caveat)
    {
      $caveatMet = false;
      if ($caveat->isFirstParty())
        $caveatMet = $this->verifyFirstPartyCaveat($caveat);
      else if ($caveat->isThirdParty())
        $caveatMet = $this->verifyThirdPartyCaveat($caveat, $macaroon, $dischargeMacaroons);
      if (!$caveatMet)
        throw new \DomainException("Caveat not met. Unable to satisfy: {$caveat->getCaveatId()}");
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

  // TODO: Implement
  private function verifyThirdPartyCaveat(Caveat $caveat, Macaroon $root, Array $dischargeMacaroons)
  {
    return false;
  }

  /**
   * returns the derived key from the caveat verification id
   * @param  string $signature
   * @param  Caveat $caveat
   * @return string
   */
  private function extractCaveatKey($signature, Caveat $caveat)
  {
    $key            = Utils::truncateOrPad($signature);
    $verificationId = base64_decode($caveat->getVerificationId());
    return \Sodium::crypto_secretbox_open($verificationId, $nonce, $key);
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
