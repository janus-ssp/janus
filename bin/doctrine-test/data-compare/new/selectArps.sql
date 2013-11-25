SELECT      CR.eid,
            CR.revisionid,
            CR.arp_attributes as arpAttributes
FROM janus__connectionRevision AS CR

WHERE   CR.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CR.eid
  )
ORDER BY  CR.eid
