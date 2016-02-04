<?php

namespace Macaroons\Serializers;

/**
 * Simple value object that represents a component of a Macaroon, such as
 * a Caveat's verification identifier
 */
class Packet
{
  const PACKET_PREFIX_LENGTH = 4;

  /**
   * key for packet
   * @var string
   */
  private $_key;

  /**
   * packet data
   * @var string
   */
  private $_data;

  /**
   * Creates a new Packet with key and value
   * @param string $key
   * @param string $data
   */
  public function __construct($key = NULL, $data = NULL)
  {
    $this->_key = $key;
    $this->_data = $data;
  }

  /**
   * encodes an array of packets into a string
   * @param  Array|array $packets
   * @return string
   */
  public function packetize(Array $packets)
  {
    $self = $this;
    return join('',
                    array_map(
                                function($key, $data) use($self) {
                                  return $this->encode($key, $data);
                                },
                                array_keys($packets),
                                $packets
                              )
                );
  }

  /**
   * returns packet's key, e.g. vid
   * @return string
   */
  public function getKey()
  {
    return $this->_key;
  }

  /**
   * returns data of packet
   * @return string
   */
  public function getData()
  {
    return $this->_data;
  }

  /**
   * encodes and pads packet into string representation for serialization
   * @param  string $key
   * @param  string $data
   * @return string
   */
  private function encode($key, $data)
  {
    $packetSize = self::PACKET_PREFIX_LENGTH + 2 + strlen($key) + strlen($data);
    // packetSize can't be larger than 0xFFFF
    if ($packetSize > pow(16, 4))
      throw new \InvalidArgumentException('Data is too long for a binary packet.');

    // hexadecimal representation with lowercase letters
    $packetSizeHex = sprintf("%x", $packetSize);
    $header = str_pad($packetSizeHex, 4, '0', STR_PAD_LEFT);
    $packetContent = "$key $data\n";
    $packet = $header . $packetContent;
    return $packet;
  }

  /**
   * decodes serialized version of packet into a Packet
   * @param  string $packet
   * @return Packet
   */
  public function decode($packet)
  {
    $packets = explode(' ', $packet);
    $key = array_shift($packets);
    $data = substr($packet, strlen($key) + 1);
    return new Packet($key, $data);
  }
}
