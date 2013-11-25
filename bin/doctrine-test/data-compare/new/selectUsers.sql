SELECT      CR.eid,
            CR.revisionid,
            U.userid AS username
FROM janus__connectionRevision AS CR

-- Get users
INNER JOIN   janus__hasConnection AS UCR
    ON      UCR.eid = CR.eid
INNER JOIN   janus__user AS U
    ON      U.uid = UCR.uid

WHERE   CR.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CR.eid
  )
ORDER BY  CR.eid,
          U.userid

