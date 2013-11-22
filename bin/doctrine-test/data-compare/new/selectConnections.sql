SELECT      CR.eid,
            CR.entityid,
            CR.revisionid,
            CR.state,
            CR.type,
            CR.expiration,
            CR.metadataurl,
            CR.metadata_valid_until,
            CR.metadata_cache_until,
            CR.allowedall,
            CR.manipulation,
            CR.user,
            CR.created,
            CR.ip,
            CR.parent,
            CR.revisionnote,
            CR.active,
            NULL AS arpAttributes,
            NULL AS allowedConnections,
            NULL AS disableConsentConnections,
            NULL AS metadata,
            NULL AS users,
          'old'
FROM janus__connectionRevision AS CR
WHERE   CR.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CR.eid
  )
ORDER BY  CR.eid