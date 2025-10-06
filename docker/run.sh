#!/bin/bash
set -e

docker-compose --env-file .env.local up -d
