SELECT      ER.eid,
            ER.revisionid,
            DCE.entityid AS disableConsentEntityid
FROM janus__entity AS ER

-- Get disabled consent
INNER JOIN   janus__disableConsent AS DCER
    ON      DCER.eid = ER.eid
    AND     DCER.revisionid = ER.revisionid
INNER JOIN   janus__entity AS DCE
    ON      DCE.entityid = DCER.remoteentityid
    AND     DCE.revisionid = (
        SELECT      MAX(revisionid)
        FROM        janus__entity
        WHERE       eid = DCE.eid
    )

WHERE   ER.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = ER.eid
  )
ORDER BY  ER.eid,
          DCE.eid

