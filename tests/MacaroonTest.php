<?php
namespace Macaroons\Tests;

class MacaroonTest extends \PHPUnit_Framework_TestCase
{
  protected $m;

  protected function setUp()
  {
    $this->m = new \Macaroons\Macaroon(
                                      'this is our super secret key; only we should know it',
                                      'we used our secret key',
                                      'https://mybank/'
                                      );
  }

  public function testWithoutCaveatsTest()
  {
    $this->assertEquals('e3d9e02908526c4c0039ae15114115d97fdd68bf2ba379b342aaf0f617d0552f', $this->m->getSignature());
    return $m;
  }
}
