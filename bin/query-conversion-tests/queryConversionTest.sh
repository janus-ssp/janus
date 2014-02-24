#Test script to help converting often used queries
MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf janus_migrations_test"

# Correlate entities with current metadata
$MYSQL_BIN <<SQL
    SELECT cr.eid,cr.revisionid,cr.allowedall,cr.entityid,m1.value AS name
    FROM janus__connectionRevision AS cr
    LEFT JOIN janus__metadata AS m1
    ON m1.connectionRevisionId = cr.id
    AND m1.key="name:en"
    WHERE cr.revisionid = (
    SELECT MAX(revisionid) FROM janus__connectionRevision AS e2 WHERE cr.eid=e2.eid
    )
    AND cr.state="prodaccepted"
    ORDER BY cr.eid
SQL
