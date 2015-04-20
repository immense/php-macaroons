<?php

namespace Macaroon\Tests;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
  public function testHex()
  {
    $this->assertEquals(\Macaroon\Utils::hex(\Macaroon\Utils::hmac('foo', 'testing')), '385C1BFCDAF0792D323FA4B069B965949DF1407A24705F2C239BB6B600FD1FC9');
  }

  public function testUnhex()
  {
    $this->markTestSkipped('Implement testing unhex utility function');
  }
}
