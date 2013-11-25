SELECT      ER.eid,
            ER.entityid,
            ER.revisionid,
            ER.state,
            ER.type,
            ER.expiration,
            ER.metadataurl,
            ER.metadata_valid_until,
            ER.metadata_cache_until,
            ER.allowedall,
            ER.manipulation,
            ER.user,
            ER.created,
            ER.ip,
            ER.parent,
            ER.revisionnote,
            ER.active,
            NULL AS arpAttributes,
            NULL AS allowedConnections,
            NULL AS disableConsentConnections,
            NULL AS metadata,
            NULL AS users,
          'old'
FROM janus__entity AS ER
WHERE   ER.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = ER.eid
  )
ORDER BY  ER.eid