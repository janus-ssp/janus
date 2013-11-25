SELECT      CR.eid,
            CR.revisionid,
            MD.key,
            MD.value
FROM janus__connectionRevision AS CR

-- Get metadata
INNER JOIN   janus__metadata AS MD
    ON      MD.connectionRevisionId = CR.id

WHERE   CR.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CR.eid
  )
ORDER BY  CR.eid,
          MD.key
