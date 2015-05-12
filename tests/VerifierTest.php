<?php

namespace Macaroons\Tests;

use Macaroons\Utils;
use Macaroons\Macaroon;
use Macaroons\Verifier;

use Macaroons\Exceptions\SignatureMismatchException;
use Macaroons\Exceptions\CaveatUnsatisfiedException;

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

  // public function testSatisfyExactRequiresPredicate()
  // {
  //   // Change error reporting for this test only
  //   // phpunit must be run in isolation to prevent side effects
  //   ini_set('error_reporting', E_ALL & ~E_WARNING);
  //   $this->setExpectedException('InvalidArgumentException');
  //   $this->verifier->satisfyExact();
  // }

  public function testPredicatesAreAddedForSatisfyExact()
  {
    $this->verifier->satisfyExact('test = caveat');
    $expectedPredicates = array('test = caveat');
    $this->assertEquals($expectedPredicates, $this->verifier->getPredicates());
  }

  // public function testSatisfyGeneralRequiresCallbackParameter()
  // {
  //   // Change error reporting for this test only
  //   // phpunit must be run in isolation to prevent side effects
  //   ini_set('error_reporting', E_ALL & ~E_WARNING);
  //   $this->setExpectedException('InvalidArgumentException');
  //   $this->verifier->satisfyGeneral();
  // }

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
    $this->setExpectedException('Macaroons\Exceptions\SignatureMismatchException');
    $m = new Macaroon(
                      'this is not our super secret key;',
                      'we used our secret key',
                      'https://mybank/'
                      );
    $this->verifier->verify($m, $this->secretKey);
  }

  public function testShouldVerifyMacaroonWithFirstPartyCaveatsWithPredicate()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->verifier->satisfyExact('test = caveat');
    $this->assertTrue($this->verifier->verify($this->m, $this->secretKey));
  }

  public function testShouldVerifyMacaroonWithFirstPartyCaveatsWithCallback()
  {
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->verifier->satisfyGeneral(function($caveatIdentifier){
      return Utils::startsWith($caveatIdentifier, 'test =');
    });
    $this->assertTrue($this->verifier->verify($this->m, $this->secretKey));
  }

  public function testShouldNotVerifyMacaroonWithInvalidFirstPartyCaveats()
  {
    $this->setExpectedException('Macaroons\Exceptions\CaveatUnsatisfiedException');
    $this->m->addFirstPartyCaveat('test = caveat');
    $this->verifier->satisfyExact('test = foobar');
    $this->assertTrue($this->verifier->verify($this->m, $this->secretKey));
  }

  public function testShouldVerifyMacaroonWithThirdPartyCaveats()
  {
    $this->m->addFirstPartyCaveat('account = 3735928559');
    $caveatKey  = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);

    $discharge  = new Macaroon(
                                $caveatKey,
                                $caveatId,
                                $caveatLocation
                              );
    $discharge->addFirstPartyCaveat('time < 2015-01-01T00:00');
    $protectedDischarge = $this->m->prepareForRequest($discharge);

    $this->verifier->satisfyExact('account = 3735928559');
    $this->verifier->satisfyExact('time < 2015-01-01T00:00');
    $this->assertTrue(
                      $this->verifier->verify(
                                              $this->m,
                                              $this->secretKey,
                                              array($protectedDischarge)
                                              )
                      );
  }

  public function testShouldNotVerifyMacaroonWithIncorrectKey()
  {
    $this->setExpectedException('Macaroons\Exceptions\SignatureMismatchException');
    $this->m->addFirstPartyCaveat('account = 3735928559');
    $caveatKey  = '4; guaranteed random by a fair toss of the dice';
    $caveatId = 'this was how we remind auth of key/pred';
    $caveatLocation = 'https://auth.mybank/';
    $this->m->addThirdPartyCaveat($caveatKey, $caveatId, $caveatLocation);

    $discharge  = new Macaroon(
                                $caveatKey,
                                $caveatId,
                                $caveatLocation
                              );
    $discharge->addFirstPartyCaveat('time < 2015-01-01T00:00');
    $protectedDischarge = $this->m->prepareForRequest($discharge);

    $this->verifier->satisfyExact('account = 3735928559');
    $this->verifier->satisfyExact('time < 2015-01-01T00:00');
    $this->assertTrue(
                      $this->verifier->verify(
                                              $this->m,
                                              'wrong key',
                                              array($protectedDischarge)
                                              )
                      );
  }
}
