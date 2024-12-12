#!/bin/bash


cd /data/SilMock/tests
# This should match the value in docker-compose.yml
DOMAIN_NAMES=groups.example.org,example.org ./phpunit
