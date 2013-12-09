# Clears or refreshes all kinds of cache

# Write the doctrine proxies
sudo chmod -R 777 doctrine/proxy
./bin/doctrine orm:generate-proxies

# Clear all doctrine cache
./bin/doctrine orm:clear-cache:metadata
./bin/doctrine orm:clear-cache:query
./bin/doctrine orm:clear-cache:result

# Clear all serializer cache
sudo chmod -R 777 cache
sudo rm -rf cache/serializer/annotations/*
sudo rm -rf cache/serializer/metadata/*

# Clear symfony cache
sudo chmod -R 777 app/cache
sudo sudo rm -rf app/cache/*

# Clear symfony logs
sudo chmod -R 777 app/logs
sudo rm -rf app/logs/*
