SELECT      CR.eid,
            CR.revisionid,
            AC.name AS allowedEntityid
FROM janus__connectionRevision AS CR

-- Get allowed connections
INNER JOIN   janus__allowedConnection AS ACR
    ON      ACR.connectionRevisionId = CR.id
INNER JOIN   janus__connection AS AC
    ON      AC.id = ACR.remoteeid

WHERE   CR.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CR.eid
  )
ORDER BY  CR.eid,
          AC.id

