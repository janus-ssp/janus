# Creates a database using Doctrine Migrations and compares it with the original sql
# To use this create a my.cnf with you credentials
#
# NOTE: before running this, change your database name to: 'janus_migrations_test'

MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf"
MYSQLDUMP_BIN="mysqldump --defaults-extra-file=$HOME/my.cnf"

echo "Importing doctrine export"
    # Create new database from doctrine model
    echo 'drop database janus_migrations_test'  | $MYSQL_BIN
    echo 'create database janus_migrations_test CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
    # Comment following line to test installing instead of upgrading
    #$MYSQL_BIN janus_migrations_test < bin/doctrine-test/pre-doctrine-schema.sql

    # Exec migrations
    ./bin/doctrine migrations:migrate --no-interaction

    # Dump migrations
    $MYSQLDUMP_BIN --no-data janus_migrations_test > /tmp/janus_migrations_test.sql

echo "Check differences between migrations and schematool, there should be none otherwise the models do not map to the db"
    echo 'drop database janus_schematool_test'  | $MYSQL_BIN
    echo 'create database janus_schematool_test CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN

    $MYSQL_BIN janus_schematool_test < /tmp/janus_migrations_test.sql

    ./bin/doctrine orm:schema-tool:update --dump-sql > /tmp/janus_schematool_test.sql
    # fix Doctrine removing quotes...
    sed -i 's/\ update\ /\ `update`\ /' /tmp/janus_schematool_test.sql
    sed -i 's/\ read\ /\ `read`\ /' /tmp/janus_schematool_test.sql
    $MYSQL_BIN janus_schematool_test < /tmp/janus_schematool_test.sql

    $MYSQLDUMP_BIN --no-data janus_schematool_test > /tmp/janus_schematool_test-dump.sql

    colordiff -u /tmp/janus_migrations_test.sql /tmp/janus_schematool_test-dump.sql

echo "Importing Janus sql"
    echo 'drop database janus_wayf'  | $MYSQL_BIN
    echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
    $MYSQL_BIN janus_wayf < bin/doctrine-test/pre-doctrine-schema.sql
    $MYSQLDUMP_BIN --no-data janus_wayf > /tmp/janus_wayf.sql

#ignore unimportant text differences, Docrine creates larger text fields by default, these cause only  little overhead,
    # can be changed back if really required, not really important for janus since it will not contain many records
    #   sed -i 's/ mediumtext/ longtext/' /tmp/janus_wayf.sql
    #sed -i 's/ text/ longtext/' /tmp/janus_wayf.sql

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

    $MYSQLDUMP_BIN --no-data janus_migrations_test > /tmp/janus_migrations_test.sql
    sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql
    sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_migrations_test.sql

    $MYSQLDUMP_BIN --no-data janus_wayf > /tmp/janus_wayf.sql

    colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql