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
    $derivedKey = \Macaroons\Utils::hex( \Macaroons\Utils::generateDerivedKey('this is our super secret key; only we should know it') );
    $this->assertEquals('A96173391E6BFA0356BBF095621B8AF1510968E770E4D27D62109B7DC374814B', $derivedKey );
  }

  public function testTruncatingString()
  {
    $this->assertEquals('foo', \Macaroons\Utils::truncateOrPad('foo bar baz', 3));
  }

  public function testPaddingString()
  {
    $this->assertEquals("foo\0\0", \Macaroons\Utils::truncateOrPad('foo', 5));
  }

  public function testSignFirstPartyCaveat()
  {
    $this->assertEquals('640A95D6147FEAB414CBAA6D392E14FFE20042AB45DDC69D02008C0E96B2F5E7', \Macaroons\Utils::hex(\Macaroons\Utils::signFirstPartyCaveat('e3d9e02908526c4c0039ae15114115d97fdd68bf2ba379b342aaf0f617d0552f', 'test = caveat')));
  }

  public function testSignThirdPartyCaveat()
  {
    $caveatKey = '4; guaranteed random by a fair toss of the dice';
    $signature = \Macaroons\Utils::unhex('E3D9E02908526C4C0039AE15114115D97FDD68BF2BA379B342AAF0F617D0552F');
    $verificationId = 'vr3nasU9MYnXlim9hhz7dtnvSVLrIFKsfLs8rGPkF8ZqOkh5oulg5ZHWDZeFw7CN';
    $caveatId = 'this was how we remind auth of key/pred';
    $v_hmac = \Macaroons\Utils::hmac($signature, $verificationId);
    $c_hmac = \Macaroons\Utils::hmac($signature, $caveatId);
    $combined = $v_hmac . $c_hmac;
    $new_sig = \Macaroons\Utils::hmac($signature, $combined);
    $t_signature = \Macaroons\Utils::hex(\Macaroons\Utils::signThirdPartyCaveat($signature, $verificationId, $caveatId));
    $this->assertEquals('1D0F625B1773FB4B5DB01FD815F96E4CEF1826B3270BED7512B2CBA7C7F18896', $t_signature);
  }
}
