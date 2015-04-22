<?php

namespace Macaroons;

class Utils
{
  public static function hex($value)
  {
    return join('', array_map(function($byte){
        return sprintf("%02X", $byte);
      }, unpack('C*', $value)));
  }

  public static function unhex($value)
  {
    return pack('H*', $value);
  }

  public static function hmac($key, $data, $digest = 'sha256')
  {
    return hash_hmac($digest, $data, $key, true);
  }

  public static function generateDerivedKey($key)
  {
    return self::hmac('macaroons-key-generator', $key);
  }
}
