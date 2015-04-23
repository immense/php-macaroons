## Macaroons

This is a PHP implementation of Macaroons. It is still under active development.

## Requirements

* [PHP >= 5.3.6](http://php.net)
* [libsodium](https://github.com/jedisct1/libsodium)
* [libsodium-php](https://github.com/jedisct1/libsodium-php)

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

* Install the latest version as a project depedency
  ```bash
  composer require immense/macaroons
  ```

## Tests

Files must end with `Test` e.g. `ClassTest.php`

* Run tests from the project root
  ```bash
  phpunit
  ```
