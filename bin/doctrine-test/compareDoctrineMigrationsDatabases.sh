# Creates a database using Doctrine Migrations and compares it with the original sql

# NOTE: before running this, change your database name to: 'janus_migrations_test'

echo "Importing doctrine export"
# Create new database from doctrine model
echo 'drop database janus_migrations_test' | mysql -uroot -p
echo 'create database janus_migrations_test CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -uroot -p
./bin/doctrine migrations:migrate --no-interaction

# Remove collation
# Replace text types
mysqldump -uroot -p --no-data janus_migrations_test > /tmp/janus_migrations_test.sql

echo "Importing Janus sql"
echo 'drop database janus_wayf' | mysql -uroot -p
echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -uroot -p
mysql -uroot -p janus_wayf < docs/janus.sql
mysqldump -uroot -p --no-data janus_wayf > /tmp/janus_wayf.sql

#ignore unimportant text differences, Docrine creates larger text fields by default, these cause only  little overhead,
# can be changed back if really required, not really important for janus since it will not contain many records
sed -i 's/ mediumtext/ longtext/' /tmp/janus_wayf.sql
sed -i 's/ text/ longtext/' /tmp/janus_wayf.sql

# Remove collations to reduce diff, all tables are utf8 anyway and collation will be changed to unicode, which is a good thing:
# http://forums.mysql.com/read.php?103,187048,188748#msg-188748
sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_wayf.sql
sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql
sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_migrations_test.sql

# Remove comma's to reduce diff
sed -i 's/,$//' /tmp/janus_wayf.sql
sed -i 's/,$//' /tmp/janus_migrations_test.sql

colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql

echo "Test reverse migration"

./bin/doctrine migrations:migrate --no-interaction 0

mysqldump -uroot -p --no-data janus_migrations_test > /tmp/janus_migrations_test.sql
sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql
sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_migrations_test.sql

mysqldump -uroot -p --no-data janus_wayf > /tmp/janus_wayf.sql

colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql