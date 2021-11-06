#!/usr/bin/bash

DOCKER_BUILDKIT=1

echo -e -n "\n--- Build docker image ---\n\n"
docker build -t newsletter-test .

echo -e -n "\n--- PHP version ---\n\n"
docker run --rm --init newsletter-test -v

echo -e -n "\n--- Test 1: Get contributors section ---\n\n"
docker run --rm --init newsletter-test src/contributors_statistics.php

# check is valid or not?
# test2: check valid content created or not? (html)

echo -e -n "\n--- Test 2: Get HTML export ---\n\n"
# docker run --rm --init newsletter-test src/contributors_statistics.php

