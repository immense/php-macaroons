#!/bin/sh
set -ex

LIBSODIUM_DIR=$HOME/libsodium

# caching
if [ ! -d "$LIBSODIUM_DIR/lib" ]; then
  LIBSODIUM_VERSION=1.0.10
  curl -L https://github.com/jedisct1/libsodium/releases/download/$LIBSODIUM_VERSION/libsodium-$LIBSODIUM_VERSION.tar.gz | tar zx
  cd libsodium-$LIBSODIUM_VERSION && ./configure --prefix=$LIBSODIUM_DIR && make && make check
  make install
else
  echo 'Using cached directory.'
fi

LIBSODIUM_PHP_VERSION=1.0.2
LIBSODIUM_DIR=$LIBSODIUM_DIR pecl install -f libsodium-$LIBSODIUM_PHP_VERSION
composer self-update
