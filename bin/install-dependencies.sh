#!/bin/sh
set -ex

LIBSODIUM_DIR=$HOME/libsodium

# caching
if [ ! -d "$LIBSODIUM_DIR/lib" ]; then
  curl -L https://github.com/jedisct1/libsodium/releases/download/1.0.10/libsodium-1.0.10.tar.gz | tar zx
  cd libsodium-1.0.10 && ./configure --prefix=$LIBSODIUM_DIR && make && make check
  make install
else
  echo 'Using cached directory.'
fi

LIBSODIUM_DIR=$LIBSODIUM_DIR pecl install -f libsodium-1.0.2
composer self-update
