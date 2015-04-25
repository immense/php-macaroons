<?php

namespace Macaroons\Tests;

use Macaroons\Utils;
use Macaroons\Macaroon;
use Macaroons\Packet;
use Macaroons\Verifier;

class VerifierTest extends \PHPUnit_Framework_TestCase
{
  private $verifier;
  public function setUp()
  {
    $this->verifier = new Verifier();
  }

  public function testVerifier()
  {
    $this->assertNotNull($this->verifier);
  }

  public function testSatisfyExactRequiresPredicate()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->verifier->satisfyExact();
  }

  public function testPredicatesAreAddedForSatisfyExact()
  {
    $this->verifier->satisfyExact('test = caveat');
    $this->assertEquals(array('test = caveat'), $this->verifier->getPredicates());
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
    $this->markTestSkipped('TODO');
  }

  public function testShouldNotVerifyInvalidRootMacaroon()
  {
    $this->markTestSkipped('TODO');
  }

  public function testShouldVerifyMacaroonWithFirstPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

  public function testShouldVerifyMacaroonWithThirdPartyCaveats()
  {
    $this->markTestSkipped('TODO');
  }

  public function testShouldNotVerifyMacaroonWithInvalidFirstPartyCaveats()
  {
    // $this->setExpectedException('DomainException');
    $this->markTestSkipped('TODO');
  }

  public function testShouldNotVerifyMacaroonWithInvalidThirdPartyCaveats()
  {
    // $this->setExpectedException('DomainException');
    $this->markTestSkipped('TODO');
  }
}
