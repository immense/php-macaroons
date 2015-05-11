<?php
namespace Macaroons\Tests;

use Macaroons\Utils;
use Macaroons\Macaroon;
use Macaroons\Serializers\Packet;

class PacketTest extends \PHPUnit_Framework_TestCase
{
  public function testPacketizeThrowsInvalidArgumentException()
  {
    $this->setExpectedException('InvalidArgumentException');
    $packet = new Packet();
    $packet->packetize(
                        array(
                          'key' => str_repeat('0', pow(16, 4))
                        )
                      );
  }

  public function testPacketSizePadding()
  {
    $packet = new Packet();
    $packetStr = $packet->packetize(
                        array(
                          'a' => 'b'
                        )
                      );
    $this->assertEquals("0008a b\n", $packetStr);
  }

  public function testPacketDecoding()
  {
    $strippedPacket = "a b";
    $packet = new Packet();
    $packet = $packet->decode($strippedPacket);
    $this->assertEquals('a', $packet->getKey());
    $this->assertEquals('b', $packet->getData());
  }
}
