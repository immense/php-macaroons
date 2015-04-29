<?php

namespace Macaroons;

class Packet
{
  const PACKET_PREFIX_LENGTH = 4;
  private $key;
  private $data;
  public function __construct($key = NULL, $data = NULL)
  {
    $this->key = $key;
    $this->data = $data;
  }

  public function packetize(Array $packets)
  {
    // PHP 5.3 workaround
    // $this isn't bound in anonymous functions
    return join('',
                    array_map(
                                array($this, 'mapPacketsCallback'),
                                array_keys($packets),
                                $packets
                              )
                );
  }

  public function getKey()
  {
    return $this->key;
  }

  public function getData()
  {
    return $this->data;
  }

  private function mapPacketsCallback($key, $data)
  {
    return $this->encode($key, $data);
  }

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

  public function decode($packet)
  {
    $packets = explode(' ', $packet);
    $key = array_shift($packets);
    $data = substr($packet, strlen($key) + 1);
    return new Packet($key, $data);
  }
}
