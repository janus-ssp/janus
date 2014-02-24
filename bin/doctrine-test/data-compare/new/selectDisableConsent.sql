SELECT      CR.eid,
            CR.revisionid,
            DCC.name AS disableConsentEntityid
FROM janus__connection AS C
INNER JOIN janus__connectionRevision AS CR
  ON CR.eid = C.id
  AND CR.revisionid = C.revisionNr


-- Get disabled consent
INNER JOIN   janus__disableConsent AS DCCR
    ON      DCCR.connectionRevisionId = CR.id
INNER JOIN   janus__connection AS DCC
    ON      DCC.id = DCCR.remoteeid

ORDER BY  CR.eid,
          DCC.id

