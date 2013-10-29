#!/bin/sh

# NOTE: before running this, change your database name to: 'janus_migrations_test'

MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf"
MYSQLDUMP_BIN="mysqldump --defaults-extra-file=$HOME/my.cnf"

echo "Recreating 'janus_migrations_test' database"
#echo 'drop database janus_migrations_test'  | $MYSQL_BIN
#echo 'create database janus_migrations_test CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN

# Exec migrations
#./bin/doctrine migrations:migrate --no-interaction

php bin/doctrine-test/testDoctrine.php