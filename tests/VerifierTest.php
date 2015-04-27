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
  public function setUp()
  {
    $this->verifier = new Verifier();
    $this->secretKey = 'this is our super secret key; only we should know it';
  }

  public function testSatisfyExactRequiresPredicate()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->verifier->satisfyExact();
  }

  public function testPredicatesAreAddedForSatisfyExact()
  {
    $this->verifier->satisfyExact('test = caveat');
    $expectedPredicates = array('test = caveat');
    $this->assertEquals($expectedPredicates, $this->verifier->getPredicates());
  }

  public function testSatisfyGeneralRequiresCallbackParameter()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->verifier->satisfyGeneral();
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
    $m = new Macaroon(
                      $this->secretKey,
                      'we used our secret key',
                      'https://mybank/'
                      );
    $this->assertTrue($this->verifier->verify($m, $this->secretKey));
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
    $this->markTestSkipped('TODO');
  }

  public function testShouldVerifyMacaroonWithFirstPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

  public function testShouldNotVerifyMacaroonWithInvalidFirstPartyCaveats()
  {
    // $this->setExpectedException('DomainException');
    $this->markTestSkipped('TODO');
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
