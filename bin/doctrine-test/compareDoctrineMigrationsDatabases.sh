# Creates a database using Doctrine Migrations and compares it with the original sql

echo "Importing doctrine export"
# Create new database from doctrine model
echo 'drop database janus_migrations_test' | mysql -uroot -p
echo 'create database janus_migrations_test CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -uroot -p
./bin/doctrine migrations:migrate --no-interaction # --write-sql > /tmp/doctrine-migrations-create.sql

# Remove collation
# Replace text types
sed -i 's/LONGTEXT/TEXT/g' /tmp/doctrine-create.sql
mysql -uroot -p janus_migrations_test < /tmp/doctrine-create.sql
mysqldump -uroot -p --no-data janus_migrations_test > /tmp/janus_migrations_test.sql

echo "Importing Janus sql"
echo 'drop database janus_wayf' | mysql -uroot -p
echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -uroot -p
mysql -uroot -p janus_wayf < docs/janus.sql
mysqldump -uroot -p --no-data janus_wayf > /tmp/janus_wayf.sql

#ignore unimportant text differences
sed -i 's/ mediumtext/ longtext/' /tmp/janus_wayf.sql
sed -i 's/ text/ longtext/' /tmp/janus_wayf.sql

# Remove collations to reduce diff
sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_wayf.sql
sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql
sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_migrations_test.sql

# Remove comma's to reduce diff
sed -i 's/,$//' /tmp/janus_wayf.sql
sed -i 's/,$//' /tmp/janus_migrations_test.sql

colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql
