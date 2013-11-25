SELECT      CR.eid,
            CR.revisionid,
            DCC.name AS disableConsentEntityid
FROM janus__connectionRevision AS CR

-- Get disabled consent
INNER JOIN   janus__disableConsent AS DCCR
    ON      DCCR.connectionRevisionId = CR.id
INNER JOIN   janus__connection AS DCC
    ON      DCC.id = DCCR.remoteeid

WHERE   CR.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CR.eid
  )
ORDER BY  CR.eid,
          DCC.id

