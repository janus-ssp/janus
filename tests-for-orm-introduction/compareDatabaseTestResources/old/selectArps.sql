SELECT      ER.eid,
            ER.revisionid,
            ARP.attributes as arpAttributes
FROM janus__entity AS ER

-- Get arp
INNER JOIN   janus__arp AS ARP
    ON      ARP.aid = ER.arp

WHERE   ER.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = ER.eid
  )
ORDER BY  ER.eid
