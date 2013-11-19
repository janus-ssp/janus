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

recreateDb() {
    echo "Recreating 'janus_migrations_test' database"
    echo 'drop database janus_migrations_test'  | $MYSQL_BIN
    echo 'create database janus_migrations_test CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
}

provisionDb() {
    if [ "$UPDATE_SOURCE" == "original_schema" ]; then
        echo "importing pre-surfnet merge into test db"
        $MYSQL_BIN janus_migrations_test < bin/doctrine-test/pre-surfnet-merge-schema.sql
    fi

    if [ "$UPDATE_SOURCE" == "local_dump" ]; then
        echo 'dumping local db'
        $MYSQLDUMP_BIN --compact --skip-comments serviceregistry > /tmp/serviceregistry-dump.sql

        echo 'importing copy of local db into test db'
        $MYSQL_BIN janus_migrations_test < /tmp/serviceregistry-dump.sql
    fi

    if [ "$UPDATE_SOURCE" == "live_dump" ]; then
        echo "Importing production dump into test db"
        $MYSQL_BIN janus_migrations_test < ~/janus/db_changelog.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__blockedEntity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__allowedEntity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__arp.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__attribute.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__disableConsent.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__entity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__hasEntity.sql
        $MYSQL_BIN janus_migrations_test < ~/janus/janus__metadata.sql
        $MYSQL_BIN -v janus_migrations_test < ~/janus/janus__user.sql

        # Run serviceregistry patches over prod import
        JANUS_DIR="$( cd -P "$( dirname "$0" )" && pwd )"
        $JANUS_DIR/../../../../../bin/dbpatch.php update
    fi
}

migrateUp() {
    # Exec migrations
    ./bin/doctrine migrations:migrate --no-interaction

    # Dump migrations
    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_migrations_test > /tmp/janus_migrations_test.sql

    # Remove autoincrement created by data
    sed -i 's/ AUTO_INCREMENT=[0-9]*\b//' /tmp/janus_migrations_test.sql

    # Prefix set foreign ignore statement
    echo "SET FOREIGN_KEY_CHECKS = 0;"|cat - /tmp/janus_migrations_test.sql > /tmp/out && mv /tmp/out /tmp/janus_migrations_test.sql
}

compareWithSchemaTool() {
    echo "Check differences between migrations and schematool, there should be none otherwise the models do not map to the db"
    ./bin/doctrine orm:schema-tool:update --dump-sql > /tmp/janus_schematool_update.sql
    # fix Doctrine removing quotes...
    sed -i 's/\ update\ /\ `update`\ /' /tmp/janus_schematool_update.sql
    sed -i 's/\ read\ /\ `read`\ /' /tmp/janus_schematool_update.sql
    sed -i 's/\ key\ /\ `key`\ /' /tmp/janus_schematool_update.sql
    echo "Creating test db"
    $MYSQL_BIN -e  "drop database janus_schematool_test"
    $MYSQL_BIN -e  "create database janus_schematool_test CHARSET=utf8 COLLATE=utf8_unicode_ci"

    echo "loading current db state in test db"
    $MYSQL_BIN janus_schematool_test < /tmp/janus_migrations_test.sql
    echo "Applying the following changes from doctrine schematool update:"
    cat /tmp/janus_schematool_update.sql
    $MYSQL_BIN janus_schematool_test < /tmp/janus_schematool_update.sql

    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_schematool_test > /tmp/janus_schematool_test_dump.sql

    colordiff -u /tmp/janus_migrations_test.sql /tmp/janus_schematool_test_dump.sql
}

compareWithJanus() {
    echo "Importing Janus sql"
    echo 'drop database janus_wayf'  | $MYSQL_BIN
    echo 'create database janus_wayf CHARSET=utf8 COLLATE=utf8_unicode_ci'  | $MYSQL_BIN
    $MYSQL_BIN janus_wayf < bin/doctrine-test/pre-surfnet-merge-schema.sql
    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_wayf > /tmp/janus_wayf.sql

    colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql
}

migrateDown() {
    echo "Test reverse migration"
    ./bin/doctrine migrations:migrate --no-interaction 0
}

compareWithOriginal() {

    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_migrations_test > /tmp/janus_migrations_test.sql
    # Remove autoincrement created by data
    sed -i 's/ AUTO_INCREMENT=[0-9]*\b//' /tmp/janus_migrations_test.sql

    $MYSQLDUMP_BIN --compact --skip-comments --no-data janus_wayf > /tmp/janus_wayf.sql


    colordiff -u /tmp/janus_wayf.sql /tmp/janus_migrations_test.sql
}

recreateDb
provisionDb
migrateUp

compareWithSchemaTool
#compareWithJanus

migrateDown

compareWithOriginal