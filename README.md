## Macaroons
[![Build Status](https://img.shields.io/travis/immense/php-macaroons.svg)](https://travis-ci.org/immense/php-macaroons)
[![HHVM Tested](https://img.shields.io/hhvm/immense/macaroons.svg)](https://travis-ci.org/immense/php-macaroons)
[![Latest Stable Version](https://img.shields.io/packagist/v/immense/macaroons.svg)](https://packagist.org/packages/immense/macaroons)
[![License](https://img.shields.io/packagist/l/immense/macaroons.svg)](https://packagist.org/packages/immense/macaroons)
[![Coverage Status](https://img.shields.io/coveralls/immense/php-macaroons.svg)](https://coveralls.io/r/immense/php-macaroons?branch=master)
[![Dependency Status](https://img.shields.io/versioneye/d/php/immense:macaroons.svg)](https://www.versioneye.com/user/projects/55c3a548653762001a002e0b)
[![Code Climate](https://codeclimate.com/github/immense/php-macaroons/badges/gpa.svg)](https://codeclimate.com/github/immense/php-macaroons)

This PHP library provides an implementation of [macaroons](http://hackingdistributed.com/2014/05/16/macaroons-are-better-than-cookies) which allow decentralized delegation, attenuation, and verification.

## Requirements

* [PHP >= 5.4.0](http://php.net)
* [libsodium 1.x](https://github.com/jedisct1/libsodium)
* [libsodium-php 1.x](https://github.com/jedisct1/libsodium-php)

## Installing `libsodium-php`

* OS X using [homebrew](https://github.com/Homebrew/homebrew)
  ```bash
  brew tap homebrew/php
  brew install php55-libsodium
  ```

* Using `pecl`
  ```bash
  pecl install libsodium
  ```

## Installation via [composer](https://getcomposer.org)

In your project directory:

* Create a `composer.json` in your project if necessary
  ```bash
  composer init
  ```

* Install the latest version as a project dependency
  ```bash
  composer require immense/macaroons
  ```

## Tests

N.B. phpunit 5 requires PHP >= 5.6

Files must end with `Test` e.g. `ClassTest.php`

* From the `php-macaroons` root directory:

  ```bash
  phpunit
  ```

* Run tests on file change (optional)
  ```bash
  gem install observr
  observr ./autotest-observr.rb
  ```

## HHVM

Currently [HHVM](http://hhvm.com) is not supported because the PHP libsodium
bindings do not support HHVM.

## License

[php-macaroons](https://github.com/immense/php-macaroons) is licensed under the MIT license. Please see the [license](MIT-LICENSE) for more information.
