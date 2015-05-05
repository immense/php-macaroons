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
    $json = '{"location":"https:\/\/mybank\/","identifier":"we used our secret key","caveats":[],"signature":"e3d9e02908526c4c0039ae15114115d97fdd68bf2ba379b342aaf0f617d0552f"}';
    $this->assertEquals($json, $this->m->toJSON());
  }

  public function testDeserializesJSON()
  {
    $deserialized = Macaroon::fromJSON($this->m->toJSON());
    $this->assertEquals($this->m->getIdentifier(), $deserialized->getIdentifier());
    $this->assertEquals($this->m->getLocation(), $deserialized->getLocation());
    $this->assertEquals($this->m->getSignature(), $deserialized->getSignature());
  }

  public function testSerializeJSONWithFirstPartyCaveat()
  {
    $this->markTestSkipped('TODO');
  }

  public function testSerializeJSONWithMultipleFirstPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

  public function testSerializeJSONWithFirstAndThirdPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

  public function testSerializeJSONWithThirdPartyCaveat()
  {
    $this->markTestSkipped('TODO');
  }

  public function testSerializeJSONWithMultipleThirdPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

}
