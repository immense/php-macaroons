<?php

namespace Macaroons\Tests;

use Macaroons\Utils;
use Macaroons\Macaroon;
use Macaroons\Packet;
use Macaroons\Verifier;

class VerifierTest extends \PHPUnit_Framework_TestCase
{
  // TODO: use a data provider
  private $verifier;
  private $secretKey;
  private $m;
  public function setUp()
  {
    $this->verifier = new Verifier();
    $this->secretKey = 'this is our super secret key; only we should know it';
    $this->m = new Macaroon(
                            $this->secretKey,
                            'we used our secret key',
                            'https://mybank/'
                            );
    $this->macaroonWithThirdPartyCaveat = new Macaroon(
                                                        $this->secretKey,
                                                        'we used our secret key',
                                                        'https://mybank/'
                                                        );
    $caveatKey = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->macaroonWithThirdPartyCaveat->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);
  }

  public function testPredicatesAreAddedForSatisfyExact()
  {
    $this->verifier->satisfyExact('test = caveat');
    $expectedPredicates = array('test = caveat');
    $this->assertEquals($expectedPredicates, $this->verifier->getPredicates());
  }

  public function testSatisfyGeneralRequiresCallbackFunction()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->verifier->satisfyGeneral('notAFunction');
  }

  public function testSatisfyGeneral()
  {
    $callback = function(){};
    $this->verifier->satisfyGeneral($callback);
    $this->assertEquals(array($callback), $this->verifier->getCallbacks());
  }

  public function testShouldVerifyRootMacaroon()
  {
    $this->assertTrue($this->verifier->verify($this->m, $this->secretKey));
  }

  public function testShouldNotVerifyInvalidRootMacaroon()
  {
    $this->setExpectedException('DomainException');
    $m = new Macaroon(
                      'this is not our super secret key;',
                      'we used our secret key',
                      'https://mybank/'
                      );
    $this->verifier->verify($m, $this->secretKey);
  }

  public function testShouldVerifyMacaroonWithFirstPartyCaveats()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->verifier->satisfyExact('test = caveat');
    $this->assertTrue($this->verifier->verify($this->m, $this->secretKey));
  }

  public function testShouldNotVerifyMacaroonWithInvalidFirstPartyCaveats()
  {
    $this->setExpectedException('DomainException');
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->verifier->satisfyExact('test = foobar');
    $this->assertTrue($this->verifier->verify($this->m, $this->secretKey));
  }

  public function testShouldVerifyMacaroonWithThirdPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

  public function testShouldNotVerifyMacaroonWithInvalidThirdPartyCaveats()
  {
    // $this->setExpectedException('DomainException');
    $this->markTestSkipped('TODO');
  }
}
