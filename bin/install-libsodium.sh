#!/bin/sh
set -ex

curl https://download.libsodium.org/libsodium/releases/libsodium-1.0.2.tar.gz | tar zx
cd libsodium-1.0.2
./configure && make && make check
sudo make install
pecl install libsodium-beta


