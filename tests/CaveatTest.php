<?php
namespace Macaroons\Tests;

use Macaroons\Macaroon;
use Macaroons\Caveat;

class CaveatTest extends \PHPUnit_Framework_TestCase
{
  // TODO: Use data provider
  private $firstPartyCaveat;
  private $thirdPartyCaveat;

  protected function setUp()
  {
    $this->firstPartyCaveat = new Caveat('we used our secret key');
    $this->thirdPartyCaveat = new Caveat('we used our secret key', 'this is the verification id', 'https://mybank/');
  }

  public function testInitialAttributesSetCorrectly()
  {
    $this->assertEquals('we used our secret key', $this->thirdPartyCaveat->getCaveatId());
    $this->assertEquals('this is the verification id', $this->thirdPartyCaveat->getVerificationId());
    $this->assertEquals('https://mybank/', $this->thirdPartyCaveat->getCaveatLocation());
  }

  public function testCaveatWithoutVerificationIdIsFirstParty()
  {
    $this->assertTrue($this->firstPartyCaveat->isFirstParty());
  }

  public function testCaveatWithVerificationIdIsThirdParty()
  {
    $this->assertTrue($this->thirdPartyCaveat->isThirdParty());
  }

}
