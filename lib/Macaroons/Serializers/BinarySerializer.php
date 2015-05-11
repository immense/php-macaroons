<?php

namespace Macaroons\Serializers;

use Macaroons\Utils;
use Macaroons\Macaroon;
use Macaroons\Caveat;

class BinarySerializer extends BaseSerializer
{
  public function serialize()
  {
    $p = new Packet();
    $s = $p->packetize(
                        array(
                          'location' => $this->macaroon->getLocation(),
                          'identifier' => $this->macaroon->getIdentifier()
                        )
                      );
    foreach ($this->macaroon->getCaveats() as $caveat)
    {
      $caveatKeys = array(
                          'cid' => $caveat->getCaveatId()
                          );
      if ($caveat->getVerificationId() && $caveat->getCaveatLocation())
      {
        $caveatKeys = array_merge(
                                  $caveatKeys,
                                  array(
                                        'vid' => $caveat->getVerificationId(),
                                        'cl' => $caveat->getCaveatLocation()
                                        )
                                  );
      }
      $p = new Packet();
      $s = $s . $p->packetize($caveatKeys);
    }
    $p = new Packet();
    $s = $s . $p->packetize(array('signature' => Utils::unhexlify($this->macaroon->getSignature())));
    return Utils::base64_url_encode($s);
  }

  public function deserialize($serialized)
  {
    $location   = NULL;
    $identifier = NULL;
    $signature  = NULL;
    $caveats    = array();
    $decoded    = Utils::base64_url_decode($serialized);
    $index      = 0;

    while ($index < strlen($decoded))
    {
      // TOOD: Replace 4 with PACKET_PREFIX_LENGTH
      $packetLength    = hexdec(substr($decoded, $index, 4));
      $packetDataStart = $index + 4;
      $strippedPacket  = substr($decoded, $packetDataStart, $packetLength - 5);
      $packet          = new Packet();
      $packet          = $packet->decode($strippedPacket);

      switch($packet->getKey())
      {
        case 'location':
          $location = $packet->getData();
        break;
        case 'identifier':
          $identifier = $packet->getData();
        break;
        case 'signature':
          $signature = $packet->getData();
        break;
        case 'cid':
          array_push($caveats, new Caveat($packet->getData()));
        break;
        case 'vid':
          $caveat = $caveats[ count($caveats) - 1 ];
          $caveat->setVerificationId($packet->getData());
        break;
        case 'cl':
          $caveat = $caveats[ count($caveats) - 1 ];
          $caveat->setCaveatLocation($packet->getData());
        break;
        default:
          throw new \DomainException('Invalid key in binary macaroon. Macaroon may be corrupted.');
        break;
      }
      $index = $index + $packetLength;
    }
    $m = new Macaroon('no_key', $identifier, $location);
    $m->setCaveats($caveats);
    $m->setSignature($signature);
    return $m;
  }
}
