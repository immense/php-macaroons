<?php
namespace Macaroons\Tests;

use Macaroons\Utils;
use Macaroons\Macaroon;
use Macaroons\Serializers\Packet;

class BinarySerializationTest extends \PHPUnit_Framework_TestCase
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

  public function testSerialize()
  {
    $binarySerialization = 'MDAxZGxvY2F0aW9uIGh0dHBzOi8vbXliYW5rLwowMDI2aWRlbnRpZmllciB3ZSB1c2VkIG91ciBzZWNyZXQga2V5CjAwMmZzaWduYXR1cmUg49ngKQhSbEwAOa4VEUEV2X_daL8ro3mzQqrw9hfQVS8K';
    $this->assertEquals($binarySerialization, $this->m->serialize());
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

  public function testDeserializeDetectsInvalidMacaroonKeys()
  {
    $p = new Packet();
    $invalidKey = Utils::base64_url_encode($p->packetize(array('foo' => 'bar')));
    $this->setExpectedException('DomainException');
    $m = Macaroon::deserialize($invalidKey);
  }
}
