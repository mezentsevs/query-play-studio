#!/bin/bash
set -e

docker-compose --env-file .env.local build
