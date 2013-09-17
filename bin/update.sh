#!/bin/sh

# Skip initial migration which creates the schema as it was when doctrine migrations was introduced
./bin/doctrine migrations:version --add 0 > /dev/null 2>&1

# Run remaining migrations
./bin/doctrine migrations:migrate --no-interaction