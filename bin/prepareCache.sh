# Clears or refreshes all kinds of cache

# Write the doctrine proxies
./app/console doctrine:generate:entities

# Clear all doctrine cache
./app/console doctrine:cache:clear-metadata
./app/console doctrine:cache:clear-query
./app/console doctrine:cache:clear-result

# Clear symfony cache
sudo chmod -R 777 app/cache
sudo rm -rfv app/cache/*

# Clear symfony logs
sudo chmod -R 777 app/logs
sudo rm -rfv app/logs/*
app/console assetic:dump
app/console assets:install