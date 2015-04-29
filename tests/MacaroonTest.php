<?php
namespace Macaroons\Tests;

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

  public function testSerialize()
  {
    $binarySerialization = 'MDAxZGxvY2F0aW9uIGh0dHBzOi8vbXliYW5rLwowMDI2aWRlbnRpZmllciB3ZSB1c2VkIG91ciBzZWNyZXQga2V5CjAwMmZzaWduYXR1cmUg49ngKQhSbEwAOa4VEUEV2X_daL8ro3mzQqrw9hfQVS8K';
    $this->assertEquals($binarySerialization, $this->m->serialize());
  }

  public function testSerializeWithPaddingStripsPadding()
  {
    $expectedBinarySerialization = 'MDAxY2xvY2F0aW9uIGh0dHA6Ly9teWJhbmsvCjAwMjZpZGVudGlmaWVyIHdlIHVzZWQgb3VyIHNlY3JldCBrZXkKMDAxOGNpZCB0ZXN0ID0gYSBjYXZlYXQKMDAyZnNpZ25hdHVyZSAOX3fqTY3ESWO6a5DZltZZReCDkfjbcdwSQDTdBrhApwo';
    $m = new Macaroon(
                      'this is our super secret key; only we should know it',
                      'we used our secret key',
                      'http://mybank/'
                      );
    $m->addFirstPartyCaveat('test = a caveat');
    $this->assertEquals($expectedBinarySerialization, $m->serialize());
  }

  public function testDeserialize()
  {
    $m = Macaroon::deserialize($this->m->serialize());
    $this->assertEquals($this->m->getSignature(), $m->getSignature());
    $this->assertEquals($this->m->getIdentifier(), $m->getIdentifier());
    $this->assertEquals($this->m->getLocation(), $m->getLocation());
  }

  public function testDeserializeBinaryWithoutPaddingShouldAddPadding()
  {
    $m = Macaroon::deserialize('MDAxY2xvY2F0aW9uIGh0dHA6Ly9teWJhbmsvCjAwMjZpZGVudGlmaWVyIHdlIHVzZWQgb3VyIHNlY3JldCBrZXkKMDAxOGNpZCB0ZXN0ID0gYSBjYXZlYXQKMDAyZnNpZ25hdHVyZSAOX3fqTY3ESWO6a5DZltZZReCDkfjbcdwSQDTdBrhApwo=');
    $this->assertEquals('0e5f77ea4d8dc44963ba6b90d996d65945e08391f8db71dc124034dd06b840a7', $m->getSignature());
  }

  public function testFirstPartyCaveat()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $expectedSignature = '197bac7a044af33332865b9266e26d493bdd668a660e44d88ce1a998c23dbd67';
    $expectedBinarySerialization = 'MDAxZGxvY2F0aW9uIGh0dHBzOi8vbXliYW5rLwowMDI2aWRlbnRpZmllciB3ZSB1c2VkIG91ciBzZWNyZXQga2V5CjAwMTZjaWQgdGVzdCA9IGNhdmVhdAowMDJmc2lnbmF0dXJlIBl7rHoESvMzMoZbkmbibUk73WaKZg5E2IzhqZjCPb1nCg';
    $this->assertEquals($expectedSignature, $this->m->getSignature());
    $this->assertEquals($expectedBinarySerialization, $this->m->serialize());
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
