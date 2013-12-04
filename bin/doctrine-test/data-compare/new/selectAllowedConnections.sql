SELECT      CR.eid,
            CR.revisionid,
            AC.name AS allowedEntityid
FROM janus__connection AS C
INNER JOIN janus__connectionRevision AS CR
  ON CR.eid = C.id
  AND CR.revisionid = C.revisionNr

-- Get allowed connections
INNER JOIN   janus__allowedConnection AS ACR
    ON      ACR.connectionRevisionId = CR.id
INNER JOIN   janus__connection AS AC
    ON      AC.id = ACR.remoteeid

ORDER BY  CR.eid,
          AC.id

