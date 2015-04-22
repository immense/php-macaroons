<?php

namespace Macaroons\Tests;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
  public function testHex()
  {
    $this->assertEquals('385C1BFCDAF0792D323FA4B069B965949DF1407A24705F2C239BB6B600FD1FC9', \Macaroons\Utils::hex(\Macaroons\Utils::hmac('foo', 'testing')));
  }

  public function testGenerateDerivedKey()
  {
    $this->assertEquals('A96173391E6BFA0356BBF095621B8AF1510968E770E4D27D62109B7DC374814B', \Macaroons\Utils::hex ( \Macaroons\Utils::generateDerivedKey('this is our super secret key; only we should know it') ) );
  }
}
