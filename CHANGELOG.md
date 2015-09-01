# PHP Macaroons Changelog

## [0.5.4] - 2015-09-01
### Changed
- Updated `phpunit` to `4.*` which is currently 4.8

### Removed
- `symfony/serializer`
- `symfony/debug`
- `symfony/stopwatch`

## [0.5.3] - 2015-05-12
### Added
- `CaveatUnsatisfiedException`
- `SignatureMismatchException`
- `InvalidMacaroonKeyException`

## [0.5.2] - 2015-05-07
### Added
- Added Caveat#__toString

### Fixed
- Fixed an issue with binary serialization/deserialization with third party caveats

## [0.5.1] - 2015-05-07
### Changed
- Switched to using the official php-coveralls
- Updated TODO.md

## [0.5.0] - 2015-05-04
### Added
- Caveat#toArray
- Test that multiple first party caveats are serialized correctly
- Macaroon#serializeJSON
- Macaroon::deserializeJSON
- Added tests for JSON serialization/deserialization

## [0.4.3] - 2015-04-30
### Added
- Added Utils::startsWith
- Added a test for verifying first party caveats using a callback

## [0.4.2] - 2015-04-30
### Removed
- Removes finishes TODOs

## [0.4.1] - 2015-04-30
### Added
- Test for Macaroon#getFirstPartyCaveats
- Test for Macaroon#getThirdPartyCaveats
- Test for invalid macaroon keys

### Fixed
- Fixes bug in Macaroon#getFirstPartyCaveats
- Fixes bug in Macaroon#getThirdPartyCaveats

## [0.4.0] - 2015-04-27
### Added
- Caveat#setCaveatLocation
- Caveat#setVerificationId
- Macaroon#getCaveats
- Macaroon#getFirstPartyCaveats
- Macaroon#getThirdPartyCaveats
- Macaroon#prepareForRequest
- Macaroon#bindSignature
- `Verifier` class
- Verifier#satisfyExact
- Verifier#satisfyGeneral
- Verifier#verifyCaveats
- Verifier#verifyFirstPartyCaveat
- Verifier#verify
- Verifier#verifyDischarge
- Added LICENSE file
- Added watchr script for TDD

### Changed
- Use array_push for code readability
- Split out base64 utility methods

### Fixed
- Fixed bugs with argument checking
- Fixed a bug when deserializing third party caveats
- Fixed libsodium integration
- Fixed missing pad in Utils::base64_url_decode
- Fixed issues using `strtr` by using `str_replace` instead

### Changed
- Increased test coverage of `Utils` class

## [0.3.4] - 2015-04-24
### Fixed
- Fixes `Verifier` skeleton test error

## [0.3.4] - 2015-04-24
### Added
- Added type hinting to `Packet` methods
- Added test skeleton for `Verifier`
- Added test skeleton for JSON serialization/deserialization
- Added tests for `Caveat` class

### Removed
- Remove unnecessary environment variables script for coveralls

### Fixed
- Fixes coveralls reporting

## [0.3.3] - 2015-04-24
### Fixed
- Fixes coveralls reporting

## [0.3.2] - 2015-04-24
### Added
- Add Travis CI, Coveralls, Packagist badges to README
- Added documentation about HHVM to README
- Added .coveralls.yml

### Fixed
- Fixed `Packet` class to be PHP 5.3 compatible when using array_map

### Removed
- HHVM from .travis.yml build matrix

## [0.3.1] - 2015-04-23
### Fixed
- Changed size argument for Utils::truncateOrPad to be optional (defaults to 32)

## [0.3.0] - 2015-04-23

Adds binary serialization and deserialization

### Added
- Utils::base64_strict_encode
- Utils::base64_url_encode
- Utils::base64_url_decode
- `Packet` class for packet data
- Packet#getKey
- Packet#getData
- Packet#packetize
- Packet#encode
- Packet#decode
- Macaroon#setSignature
- Macaroon#setCaveats
- Macaroon#serialize
- Macaroon::deserialize
- .travis.yml

### Changed
- Utils::hex renamed to Utils::hexlify
- Utils::unhex renamed to Utils::unhexlify
- phpunit.xml changed to add code coverage reporting

## [0.2.1] - 2015-04-23

## Changed
- Added composer installation instructions to README

## [0.2.0] - 2015-04-22

Adds first and third party caveats

### Added
- Caveat#getVerificationId
- Utils::truncateOrPad
- Utils::signFirstPartyCaveat
- Utils::signThirdPartyCaveat
- Macaroon#addFirstPartyCaveat
- Macaroon#addThirdPartyCaveat
- Add description field to `composer.json`
- Add repositories field to `composer.json`

### Changed
- Caveat#getId renamed Caveat#getCaveatId
- Caveat#getLocation renamed Caveat#getCaveatLocation

### Fixed
- Caveat identifier was not being set correctly

## [0.1.0] - 2015-04-22

Adds macaroon creation

### Added
- Utils::hex
- Utils::unhex
- Utils::hmac
- Utils::generateDerivedKey
- `Caveat` class for first and third party caveats
- Caveat#isFirstParty
- Caveat#isThirdParty
- `Macaroon` class
- Macaroon initial signature test
- Macaroon initial signature generation
- File with TODOs

### Changed
- Updated README
  - `libsodium-php` installation instructions
  - Updated command to run tests

### Added
- Created composer project
- Setup test suite and `phpunit.xml`

### Changed
- Changed namespace from `Macaroon` to `Macaroons`

### Fixed
- Fixed autoloading
