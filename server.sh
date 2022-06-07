#!/bin/sh
ADDR_PORT=${1:-127.0.0.1:8081}

php -S "$ADDR_PORT" -t "./public" -f router.php
