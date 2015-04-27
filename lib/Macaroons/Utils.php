<?php

namespace Macaroons;

class Utils
{
  public static function hexlify($value)
  {
    return join('', array_map(function($byte){
        return sprintf("%02X", $byte);
      }, unpack('C*', $value)));
  }

  public static function unhexlify($value)
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

  public static function truncateOrPad($str, $size = 32)
  {
    if (strlen($str) > $size)
      return substr($str, 0, $size);
    else if (strlen($str) < $size)
      return str_pad($str, $size, "\0", STR_PAD_RIGHT);
    return $str;
  }

  public static function base64_strict_encode($data)
  {
    return strtr(base64_encode($data), array("\r\n" => '', "\r" => '', "\n" => ''));
  }

  public static function base64_url_encode($data)
  {
    return rtrim(strtr(self::base64_strict_encode($data), array('+' => '-', '/' => '_')), '=');
  }

  public static function base64_url_decode($data)
  {
    return base64_decode(str_pad(strtr($data, array('-' => '+', '_' => '/')), strlen($data) % 4, '=', STR_PAD_RIGHT));
  }

  public static function signFirstPartyCaveat($signature, $predicate)
  {
    return self::hmac($signature, $predicate);
  }

  public static function signThirdPartyCaveat($signature, $verificationId, $caveatId)
  {
    $verification_id_hmac = self::hmac($signature, $verificationId);
    $caveat_id_hmac       = self::hmac($signature, $caveatId);
    $combined             = $verification_id_hmac . $caveat_id_hmac;
    return self::hmac($signature, $combined);
  }
}
