#!/usr/bin/bash

DOCKER_BUILDKIT=1

echo -e -n "\n--- Build docker image ---\n\n"
docker build -t newsletter-test .

echo -e -n "\n--- PHP version ---\n\n"
docker run --rm --init newsletter-test -v

echo -e -n "\n--- Test 1: Get contributors section ---\n\n"
docker run --rm --init newsletter-test src/contributors_statistics.php

