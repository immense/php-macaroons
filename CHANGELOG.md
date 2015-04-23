# PHP Macaroons Changelog

## [0.3.1] - 2015-04-23
### Fixed
- Changed size argument for Utils::truncateOrPad to be optional

## [0.3.0] - 2015-04-23

Adds binary serialization and deserialization

### Added
- Utils::base64_strict_encode
- Utils::base64_url_encode
- Utils::base64_url_decode
- Packet class for packet data
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
- Added comopser installation instructions to README

## [0.2.0] - 2015-04-22

Adds first and third party caveats

### Added
- Caveat#getVerificationId
- Utils::truncateOrPad
- Utils::signFirstPartyCaveat
- Utils::signThirdPartyCaveat
- Macaroon#addFirstPartyCaveat
- Macaroon#addThirdPartyCaveat

### Changed
- Caveat#getId renamed Caveat#getCaveatId
- Caveat#getLocation renamed Caveat#getCaveatLocation

### Fixed
- Caveat identifier was not being set correctly

## [Unreleased][unreleased] - 2015-04-22
### Added
- Add description field to `composer.json`
- Add repositories field to `composer.json`

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

## [Unreleased][unreleased] - 2015-04-17
### Added
- Created composer project
- Setup test suite and `phpunit.xml`

### Changed
- Changed namespace from `Macaroon` to `Macaroons`

### Fixed
- Fixed autoloading
