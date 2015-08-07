#!/bin/sh
set -ex

curl https://download.libsodium.org/libsodium/releases/libsodium-1.0.2.tar.gz | tar zx
cd libsodium-1.0.2 && ./configure --prefix=$HOME/libsodium && make && make check
make install
LIBSODIUM_DIR=$HOME/libsodium pecl install -f libsodium-0.1.3
composer self-update

