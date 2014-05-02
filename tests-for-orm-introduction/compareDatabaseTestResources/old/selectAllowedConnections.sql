SELECT      ER.eid,
            ER.revisionid,
            AE.entityid AS allowedEntityid
FROM janus__entity AS ER

-- Get allowed entities
INNER JOIN   janus__allowedEntity AS AER
    ON      AER.eid = ER.eid
    AND     AER.revisionid = ER.revisionid
INNER JOIN   janus__entity AS AE
    ON      AE.eid = AER.remoteeid
    AND     AE.revisionid = (
        SELECT      MAX(revisionid)
        FROM        janus__entity
        WHERE       eid = AE.eid
    )
WHERE   ER.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = ER.eid
  )
ORDER BY  ER.eid,
          AE.eid

