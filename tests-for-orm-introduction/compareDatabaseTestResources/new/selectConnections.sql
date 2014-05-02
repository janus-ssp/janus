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
FROM janus__connection AS C
INNER JOIN janus__connectionRevision AS CR
  ON CR.eid = C.id
  AND CR.revisionid = C.revisionNr
ORDER BY  CR.eid