SELECT      ER.eid,
            ER.revisionid,
            MD.key,
            MD.value
FROM janus__entity AS ER

-- Get metadata
INNER JOIN   janus__metadata AS MD
    ON      MD.eid = ER.eid
    AND     MD.revisionid = ER.revisionid

WHERE   ER.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = ER.eid
  )
ORDER BY  ER.eid,
          MD.key
