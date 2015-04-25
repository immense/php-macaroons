<?php
namespace Macaroons\Tests;

use Macaroons\Macaroon;

class JSONSerializationTest extends \PHPUnit_Framework_TestCase
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

  public function testJSONSerialization()
  {
    // $json = '{"id":"this is our super secret key; only we should know it","location":"we used our secret key","signature":"MDAxZGxvY2F0aW9uIGh0dHBzOi8vbXliYW5rLwowMDI2aWRlbnRpZmllciB3ZSB1c2VkIG91ciBzZWNyZXQga2V5CjAwMmZzaWduYXR1cmUg49ngKQhSbEwAOa4VEUEV2X_daL8ro3mzQqrw9hfQVS8K"}';
    // $this->assertEquals($json, $this->m->serializeJson());
    $this->markTestSkipped('JSON serialization not implemented');
  }

  public function testDeserializesJSON()
  {
    // $deserialized = Macaroon::fromJSON('{"id":"this is our super secret key; only we should know it","location":"we used our secret key","signature":"MDAxZGxvY2F0aW9uIGh0dHBzOi8vbXliYW5rLwowMDI2aWRlbnRpZmllciB3ZSB1c2VkIG91ciBzZWNyZXQga2V5CjAwMmZzaWduYXR1cmUg49ngKQhSbEwAOa4VEUEV2X_daL8ro3mzQqrw9hfQVS8K"}');
    // $this->assertEquals($this->m->getIdentifier(), $deserialized->getIdentifier());
    // $this->assertEquals($this->m->getLocation(), $deserialized->getLocation());
    // $this->assertEquals($this->m->getSignature(), $deserialized->getSignature());
    $this->markTestSkipped('JSON desserialization not implemented');
  }
}
