<?php

namespace Macaroons;

/**
 * Class to hold data pertaining to first and third party caveats
 */
class Caveat
{
  /**
   * caveat identifier
   * @var string
   */
  private $_caveat_id;

  /**
   * verification identifier
   * @var string
   */
  private $_verification_id;

  /**
   * caveat location
   * @var string
   */
  private $_caveat_location;

  /**
   * Creates a new Caveat object with an identifier.
   * @param string $caveatId       identifier of the caveat
   * @param string $verificationId third party caveat verification identifier
   * @param string $caveatLocation third party caveat location
   */
  public function __construct($caveatId, $verificationId = NULL, $caveatLocation = NULL)
  {
    $this->_caveat_id       = $caveatId;
    $this->_verification_id = $verificationId;
    $this->_caveat_location = $caveatLocation;
  }

  /**
   * caveat identifier getter
   * @return string
   */
  public function getCaveatId()
  {
    return $this->_caveat_id;
  }

  /**
   * caveat location getter
   * @return string
   */
  public function getCaveatLocation()
  {
    return $this->_caveat_location;
  }

  /**
   * verification identifier getter
   * @return string
   */
  public function getVerificationId()
  {
    return $this->_verification_id;
  }

  /**
   * caveat location setter
   * @param string $caveatLocation new value of caveat location
   * @deprecated deprecated since 1.1.0
   */
  public function setCaveatLocation($caveatLocation)
  {
    $this->_caveat_location = $caveatLocation;
  }

  /**
   * vetification identifier setter
   * @param string $verificationId new value of verification identifier
   * @deprecated deprecated since 1.1.0
   */
  public function setVerificationId($verificationId)
  {
    $this->_verification_id = $verificationId;
  }

  /**
   * Returns true when caveat is a first party caveat
   * @return boolean
   */
  public function isFirstParty()
  {
    return $this->_verification_id === NULL;
  }

  /**
   * Returns true when caveat is a third party caveat
   * @return boolean
   */
  public function isThirdParty()
  {
    return !$this->isFirstParty();
  }

  /**
   * returns array representation of caveat which can be used for serialization
   * or inspecting the caveat
   * @return Array|array
   */
  public function toArray()
  {
    $caveatKeys = array('cid' => $this->getCaveatId());
    if ($this->isThirdParty())
    {
      $caveatKeys = array_merge(
                                $caveatKeys,
                                array(
                                      'vid' => $this->getVerificationId(),
                                      'cl' => $this->getCaveatLocation()
                                      )
                                );
    }
    return $caveatKeys;
  }

  /**
   * returns string representation of caveat
   *
   * Example:
   *
   * cid fe74d78f15c0ac7bf0cdda3c48fad44e
   *
   * vid 66f9169e3dd40c2d9b244a6d77240e08
   *
   * cl example.com
   * @return string [description]
   */
  public function __toString()
  {
    $caveatAsArray = $this->toArray();
    if ($this->isThirdParty())
      $caveatAsArray['vid'] = Utils::hexlify($caveatAsArray['vid']);
    return join("\n", array_map(function($key, $value) {
      return "$key $value";
    }, array_keys($caveatAsArray), $caveatAsArray));
  }

}
