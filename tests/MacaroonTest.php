<?php
namespace Macaroons\Tests;

use Macaroons\Utils;
use Macaroons\Macaroon;

class MacaroonTest extends \PHPUnit_Framework_TestCase
{
  // TODO: Use data provider
  protected $m;

  protected function setUp()
  {
    $this->m = new Macaroon(
                                      'this is our super secret key; only we should know it',
                                      'we used our secret key',
                                      'https://mybank/'
                                      );
  }

  public function testWithoutCaveats()
  {
    $expectedSignature = 'e3d9e02908526c4c0039ae15114115d97fdd68bf2ba379b342aaf0f617d0552f';
    $this->assertEquals($expectedSignature, $this->m->getSignature());
  }

  public function testFirstPartyCaveat()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $expectedSignature = '197bac7a044af33332865b9266e26d493bdd668a660e44d88ce1a998c23dbd67';
    $expectedBinarySerialization = 'MDAxZGxvY2F0aW9uIGh0dHBzOi8vbXliYW5rLwowMDI2aWRlbnRpZmllciB3ZSB1c2VkIG91ciBzZWNyZXQga2V5CjAwMTZjaWQgdGVzdCA9IGNhdmVhdAowMDJmc2lnbmF0dXJlIBl7rHoESvMzMoZbkmbibUk73WaKZg5E2IzhqZjCPb1nCg';
    $this->assertEquals($expectedSignature, $this->m->getSignature());
    $this->assertEquals($expectedBinarySerialization, $this->m->serialize());
  }

  public function testSerializeMultipleFirstPartyCaveats()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->m->addFirstPartyCaveat('time < 2015-01-01T00:00');
    $deserializedMacaroon = Macaroon::deserialize($this->m->serialize());
    $this->assertEquals(2, count($deserializedMacaroon->getFirstPartyCaveats()));
  }

  public function testGetFirstPartyCaveats()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->assertEquals(1, count($this->m->getFirstPartyCaveats()));
  }

  public function testSerializeDeserializeThirdPartyCaveat()
  {
    $caveatKey = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);
    $m = Macaroon::deserialize($this->m->serialize());
    $this->assertEquals($this->m->getSignature(), $m->getSignature());
  }

  public function testGetThirdPartyCaveats()
  {
    $caveatKey = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);
    $this->assertEquals(1, count($this->m->getThirdPartyCaveats()));
  }

  public function testSerializeDeserializeFirstAndThirdPartyCaveats()
  {
    $this->m->addFirstPartyCaveat('account = 3735928559');
    $caveatKey = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);
    $m = Macaroon::deserialize($this->m->serialize());
    $this->assertEquals($this->m->getSignature(), $m->getSignature());
  }

  public function testInspectWithFirstAndThirdPartyCaveats()
  {
    $this->m->addFirstPartyCaveat('account = 3735928559');
    $caveatKey        = '4; guaranteed random by a fair toss of the dice';
    $caveatId         = 'this was how we remind auth of key/pred';
    $caveatLocation   = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);
    $thirdPartyCaveats = $this->m->getThirdPartyCaveats();
    $thirdPartyCaveat = array_pop($thirdPartyCaveats);
    $expected = "location {$this->m->getLocation()}\n";
    $expected .= "identifier {$this->m->getIdentifier()}\n";
    $expected .= "cid account = 3735928559\n";
    $expected .= "$thirdPartyCaveat\n";
    $expected .= "signature {$this->m->getSignature()}";
    $this->assertEquals($expected, $this->m->inspect());
  }

  public function testPrepareForRequest()
  {
    $this->m->addFirstPartyCaveat('account = 3735928559');
    $caveatKey  = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);

    $discharge  = new Macaroon(
                                $caveatKey,
                                $caveatId,
                                'https://auth.mybank/'
                              );
    $discharge->addFirstPartyCaveat('time < 2015-01-01T00:00');
    $protectedDischarge = $this->m->prepareForRequest($discharge);
    $this->assertNotEquals($discharge->getSignature(), $protectedDischarge->getSignature());
  }

  public function testBindSignature()
  {
    $this->m->addFirstPartyCaveat('account = 3735928559');
    $caveatKey  = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, 'https://auth.mybank/');

    $discharge  = new Macaroon(
                                $caveatKey,
                                $caveatId,
                                'https://auth.mybank/'
                              );
    $discharge->addFirstPartyCaveat('time < 2015-01-01T00:00');
    $boundSignature = $this->m->bindSignature($discharge->getSignature());
    $this->assertNotEquals($discharge->getSignature(), $boundSignature);
  }
}
