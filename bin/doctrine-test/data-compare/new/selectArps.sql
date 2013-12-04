SELECT      CR.eid,
            CR.revisionid,
            CR.arp_attributes as arpAttributes
FROM janus__connection AS C
INNER JOIN janus__connectionRevision AS CR
  ON CR.eid = C.id
  AND CR.revisionid = C.revisionNr

ORDER BY  CR.eid
