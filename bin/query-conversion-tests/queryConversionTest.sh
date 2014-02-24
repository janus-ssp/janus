#Test script to help converting often used queries
MYSQL_BIN="mysql --defaults-extra-file=$HOME/my.cnf janus_migrations_test"

# Correlate entities with current metadata
#$MYSQL_BIN
<<SQL
    SELECT
        cr.eid,
        cr.revisionid,
        cr.allowedall,
        cr.entityid,
        m1.value AS name
    -- Select all connections
    FROM janus__connection AS c

    -- Join latest (production ready) revisions
    INNER JOIN janus__connectionRevision AS cr
        ON  cr.eid = c.id
        AND cr.revisionid = c.revisionNr
        AND cr.state="prodaccepted"

    -- Join metadata for latest revisions
    LEFT JOIN janus__metadata AS m1
        ON m1.connectionRevisionId = cr.id
        AND m1.key="name:en"

    -- Order by connection id (== eid)
    ORDER BY c.id
SQL