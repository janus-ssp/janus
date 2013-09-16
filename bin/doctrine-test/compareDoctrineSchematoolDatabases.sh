# Creates a database using Doctrine Schemaool and compares it with the original sql
# To use this create a my.cnf with you credentials

MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf"
MYSQLDUMP_BIN="mysqldump --defaults-extra-file=$HOME/my.cnf"

echo "Importing doctrine export"
# Create new database from doctrine model
echo 'drop database janus_doctrine'  | $MYSQL_BIN
echo 'create database janus_doctrine CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
../../../vendor/bin/doctrine orm:schema-tool:create --dump-sql > /tmp/doctrine-create.sql

# Replace text types
sed -i 's/LONGTEXT/TEXT/g' /tmp/doctrine-create.sql
$MYSQL_BIN janus_doctrine < /tmp/doctrine-create.sql
$MYSQLDUMP_BIN --no-data janus_doctrine > /tmp/janus_doctrine.sql

echo "Importing Janus sql"
echo 'drop database janus_wayf'  | $MYSQL_BIN
echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
$MYSQL_BIN janus_wayf < docs/janus.sql
$MYSQL_BIN janus_wayf < docs/janus-upgrade.sql
$MYSQLDUMP_BIN --no-data janus_wayf > /tmp/janus_wayf.sql

sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_wayf.sql

sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_doctrine.sql
sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_doctrine.sql
sed -i 's/InnoDB/MyISAM/' /tmp/janus_doctrine.sql

colordiff -u /tmp/janus_wayf.sql /tmp/janus_doctrine.sql
