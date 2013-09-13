# Creates a database using Doctrine Schemaool and compares it with the original sql

echo "Importing doctrine export"
# Create new database from doctrine model
echo 'drop database janus_doctrine' | mysql -uroot -p
echo 'create database janus_doctrine CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -uroot -p
../../../vendor/bin/doctrine orm:schema-tool:create --dump-sql > /tmp/doctrine-create.sql

# Replace text types
sed -i 's/LONGTEXT/TEXT/g' /tmp/doctrine-create.sql
mysql -uroot -p janus_doctrine < /tmp/doctrine-create.sql
mysqldump -uroot -p --no-data janus_doctrine > /tmp/janus_doctrine.sql

echo "Importing Janus sql"
echo 'drop database janus_wayf' | mysql -uroot -p
echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci' | mysql -uroot -p
mysql -uroot -p janus_wayf < docs/janus.sql
mysql -uroot -p janus_wayf < docs/janus-upgrade.sql
mysqldump -uroot -p --no-data janus_wayf > /tmp/janus_wayf.sql

sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_wayf.sql

sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_doctrine.sql
sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_doctrine.sql
sed -i 's/InnoDB/MyISAM/' /tmp/janus_doctrine.sql

colordiff -u /tmp/janus_wayf.sql /tmp/janus_doctrine.sql
