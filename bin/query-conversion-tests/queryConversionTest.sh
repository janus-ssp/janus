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

# Select current ACLs
$MYSQL_BIN <<SQL
    SELECT
        cr1.eid,
        cr1.revisionid,
        cr1.entityid,
        m1.value AS name,
        acl.remoteeid,
        cr2.revisionid AS remoterevision,
        cr2.entityid as remoteentity,
        m2.value as remotename

    FROM janus__allowedConnection AS acl

    -- Join revisions linked the acl
    INNER JOIN janus__connectionRevision as cr1
        ON acl.connectionRevisionId = cr1.id

    -- Join revisions metadata
    LEFT JOIN janus__metadata AS m1
            ON m1.connectionRevisionId = cr1.id
            AND m1.key="name:en"

    -- Filter only latest revision for each connection by joining to connections
    INNER JOIN janus__connection as c1
        ON  c1.id = cr1.eid
        AND c1.revisionNr = cr1.revisionid

    -- Join remote connections linked to the acl
    INNER JOIN janus__connection as c2
        ON c2.id = acl.remoteeid

    -- Join latest revision of remote connection
    INNER JOIN janus__connectionRevision AS cr2
            ON  cr2.eid = c2.id
            AND cr2.revisionid = c2.revisionNr

    -- Join metadata for latest revion of remote connection
    LEFT JOIN janus__metadata AS m2
            ON m2.connectionRevisionId = cr2.id
            AND m2.key="name:en"

    -- Order by connection and remote connection id's (== eid)
    ORDER BY c1.id,c2.id
SQL