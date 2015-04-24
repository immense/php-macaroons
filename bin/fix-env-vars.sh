#!/bin/sh
set -ex

# Fixes php-coveralls environment variables
# Really, this should be fixed in php-coveralls
export CI_NAME=$TRAVIS_NAME
export CI_BUILD_NUMBER=$TRAVIS_BUILD_NUMBER
export CI_BUILD_URL=$TRAVIS_BUILD_URL
export CI_BRANCH=$TRAVIS_BRANCH
export CI_PULL_REQUEST=$TRAVIS_PULL_REQUEST
export CI_JOB_ID=$TRAVIS_JOB_ID
