SELECT      CR.eid,
            CR.revisionid,
            U.userid AS username
FROM janus__connection AS C
INNER JOIN janus__connectionRevision AS CR
  ON CR.eid = C.id
  AND CR.revisionid = C.revisionNr

-- Get users
INNER JOIN   janus__hasConnection AS UCR
    ON      UCR.eid = CR.eid
INNER JOIN   janus__user AS U
    ON      U.uid = UCR.uid

ORDER BY  CR.eid,
          U.userid

