#!/bin/sh
# To use this create a my.cnf with you credentials

MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf"

echo "drop database janus_migrations_test" | $MYSQL_BIN
echo "create database janus_migrations_test" | $MYSQL_BIN
../app/console doctrine:migrations:migrate --no-interaction