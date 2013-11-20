# Clears or refreshes all kinds of cache

# Write the doctrine proxies
./bin/doctrine orm:generate-proxies

# Clear all doctrine cache
./bin/doctrine orm:clear-cache:metadata
./bin/doctrine orm:clear-cache:query
./bin/doctrine orm:clear-cache:result

# Clear all serializer cache
sudo rm -rf cache/serializer/annotations/*
sudo rm -rf cache/serializer/metadata/*