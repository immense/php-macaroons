#!/bin/bash

set -e

source ~/.phpbrew/bashrc

PHP_VERSIONS=(5.3.29 5.4.45 5.5.31 5.6.17 7.0.2)

for php_version in ${PHP_VERSIONS[@]}; do
  phpbrew use $php_version
  phpunit && continue
done
