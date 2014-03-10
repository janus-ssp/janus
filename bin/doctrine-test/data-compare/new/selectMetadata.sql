SELECT      CR.eid,
            CR.revisionid,
            MD.key,
            MD.value
FROM janus__connection AS C
INNER JOIN janus__connectionRevision AS CR
  ON CR.eid = C.id
  AND CR.revisionid = C.revisionNr

-- Get metadata
INNER JOIN   janus__metadata AS MD
    ON      MD.connectionRevisionId = CR.id

ORDER BY  CR.eid,
          MD.key
