#!/bin/sh

echo "drop database janus_migrations_test" | mysql -uroot -p
echo "create database janus_migrations_test" | mysql -uroot -p
../bin/doctrine migrations:migrate --no-interaction