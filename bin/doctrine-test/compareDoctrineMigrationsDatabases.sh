# Creates a database using Doctrine Migrations and compares it with the original sql
# To use this create a my.cnf with you credentials
#
# NOTE: before running this, change your database name to: 'janus_migrations_test'

MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf"
MYSQLDUMP_BIN="mysqldump --defaults-extra-file=$HOME/my.cnf"

UPDATE_SOURCE=''

# Enable to test updating from former schema instead of installing
#UPDATE_SOURCE='original_schema';

# Enable to test updating from current schema instead of installing
UPDATE_SOURCE='local_dump'

# Enable to test updating from production schema instead of installing (requires dump files to be present
#UPDATE_SOURCE='live_dump'

    echo "Recreating 'janus_migrations_test' database"
    echo 'drop database janus_migrations_test'  | $MYSQL_BIN
    echo 'create database janus_migrations_test CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN

    if [ "$UPDATE_SOURCE" == "original_schema" ]; then
        $MYSQL_BIN janus_migrations_test < bin/doctrine-test/pre-surfnet-merge-schema.sql
    fi

    if [ "$UPDATE_SOURCE" == "local_dump" ]; then
        echo 'dumping sr db'
        $MYSQLDUMP_BIN --compact --skip-comments serviceregistry > /tmp/serviceregistry-dump.sql

        echo 'importing sr db'
        $MYSQL_BIN janus_migrations_test < /tmp/serviceregistry-dump.sql
    fi

    if [ "$UPDATE_SOURCE" == "live_dump" ]; then
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__allowedEntity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__arp.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__attribute.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__disableConsent.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__entity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__hasEntity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__metadata.sql
    fi

    # Exec migrations
    ./bin/doctrine migrations:migrate --no-interaction

    # Remove tables that clutter comparison
    $MYSQL_BIN janus_migrations_test -e "DROP TABLE IF EXISTS db_changelog"

    # Dump migrations
    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_migrations_test > /tmp/janus_migrations_test.sql

    # Remove explicit character set and collation
    sed -i 's/\ CHARACTER SET utf8//' /tmp/janus_migrations_test.sql
    sed -i 's/\ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql

    # Remove autoincrement created by data
    sed -i 's/ AUTO_INCREMENT=[0-9]*\b//' /tmp/janus_migrations_test.sql

echo "Check differences between migrations and schematool, there should be none otherwise the models do not map to the db"
    ./bin/doctrine orm:schema-tool:create --dump-sql > /tmp/janus_schematool_create.sql
    # fix Doctrine removing quotes...
    sed -i 's/\ update\ /\ `update`\ /' /tmp/janus_schematool_create.sql
    sed -i 's/\ read\ /\ `read`\ /' /tmp/janus_schematool_create.sql
    $MYSQL_BIN -e  "drop database janus_schematool_test"
    $MYSQL_BIN -e  "create database janus_schematool_test CHARSET=utf8 COLLATE=utf8_unicode_ci"
    $MYSQL_BIN janus_schematool_test < /tmp/janus_schematool_create.sql

    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_schematool_test > /tmp/janus_schematool_test_dump.sql

    # Remove collation differences
    sed -i 's/\ COLLATE utf8_unicode_ci//' /tmp/janus_schematool_test_dump.sql
    sed -i 's/\ COLLATE=utf8_unicode_ci//' /tmp/janus_schematool_test_dump.sql

    # Remove text field differences
    sed -i 's/longtext/text/' /tmp/janus_schematool_test_dump.sql

    colordiff -u /tmp/janus_migrations_test.sql /tmp/janus_schematool_test_dump.sql
    #exit

echo "Importing Janus sql"
    echo 'drop database janus_wayf'  | $MYSQL_BIN
    echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
    $MYSQL_BIN janus_wayf < bin/doctrine-test/pre-surfnet-merge-schema.sql
    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_wayf > /tmp/janus_wayf.sql

#ignore unimportant text differences, Docrine creates larger text fields by default, these cause only  little overhead,
    # can be changed back if really required, not really important for janus since it will not contain many records
    #   sed -i 's/ mediumtext/ longtext/' /tmp/janus_wayf.sql
    #sed -i 's/ text/ longtext/' /tmp/janus_wayf.sql

# Remove collations to reduce diff, all tables are utf8 anyway and collation will be changed to unicode, which is a good thing:
# http://forums.mysql.com/read.php?103,187048,188748#msg-188748
    #sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_wayf.sql
    #sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql
    #sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_migrations_test.sql

# Remove comma's to reduce diff
    sed -i 's/,$//' /tmp/janus_wayf.sql
    sed -i 's/,$//' /tmp/janus_migrations_test.sql

#colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql

echo "Test reverse migration"
    ./bin/doctrine migrations:migrate --no-interaction 0

    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_migrations_test > /tmp/janus_migrations_test.sql
    #sed -i 's/ COLLATE utf8_unicode_ci//' /tmp/janus_migrations_test.sql
    #sed -i 's/ COLLATE=utf8_unicode_ci//' /tmp/janus_migrations_test.sql
    # Remove autoincrement created by data
    sed -i 's/ AUTO_INCREMENT=[0-9]*\b//' /tmp/janus_migrations_test.sql

    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_wayf > /tmp/janus_wayf.sql


    colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql