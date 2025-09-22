#!/bin/bash


cd /data/SilMock/tests
# This should match the value in compose.yaml
DOMAIN_NAMES=groups.example.org,example.org ./phpunit
