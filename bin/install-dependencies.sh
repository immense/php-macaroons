#!/bin/sh
set -ex

LIBSODIUM_DIR=$HOME/libsodium

# caching
if [ ! -d "$LIBSODIUM_DIR/lib" ]; then
  curl https://download.libsodium.org/libsodium/releases/libsodium-1.0.2.tar.gz | tar zx
  cd libsodium-1.0.2 && ./configure --prefix=$LIBSODIUM_DIR && make && make check
  make install
else
  echo 'Using cached directory.'
fi

LIBSODIUM_DIR=$LIBSODIUM_DIR pecl install -f libsodium-0.1.3
composer self-update

