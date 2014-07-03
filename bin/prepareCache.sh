#!/bin/sh
# Clears or refreshes all kinds of cache

# Clear all doctrine cache
./app/console doctrine:cache:clear-metadata
./app/console doctrine:cache:clear-query
./app/console doctrine:cache:clear-result

# Clear symfony cache
sudo chmod -R 777 /tmp/janus/cache
sudo chmod -R 777 /var/log/janus
./app/console cache:clear