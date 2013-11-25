SELECT      ER.eid,
            ER.revisionid,
            U.userid AS username
FROM janus__entity AS ER

-- Get users
INNER JOIN   janus__hasEntity AS UER
    ON      UER.eid = ER.eid
INNER JOIN   janus__user AS U
    ON      U.uid = UER.uid

WHERE   ER.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = ER.eid
  )
ORDER BY  ER.eid,
          U.userid

