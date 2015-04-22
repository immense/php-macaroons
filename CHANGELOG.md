# PHP Macaroons Changelog

## [0.1.0] - 2015-04-22
### Added
- Utils::hex
- Utils::unhex
- Utils::hmac
- Utils::generateDerivedKey
- `Caveat` class for first and third party caveats
- `Macaroon` class
- Macaroon initial signature test
- Macaroon initial signature generation
- File with TODOs

### Changed
- Updated README
  - `libsodium-php` installation instructions
  - Updated command to run tests

## [Unreleased][unreleased]
### Added
- Created composer project
- Setup test suite and `phpunit.xml`

### Changed
- Changed namespace from `Macaroon` to `Macaroons`

### Fixed
- Fixed autoloading
