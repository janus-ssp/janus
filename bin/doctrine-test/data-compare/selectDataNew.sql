SELECT      CV.entityid,
            CV.revisionid,
            CV.state,
            CV.type,
            CV.expiration,
            CV.metadataurl,
            CV.metadata_valid_until,
            CV.metadata_cache_until,
            CV.allowedall,
            CV.manipulation,
            CV.user,
            CV.created,
            CV.ip,
            CV.parent,
            CV.revisionnote,
            CV.active,
            CV.arp_attributes as arpAttributes,
            GROUP_CONCAT(AC.name SEPARATOR "\n") as allowedConnections,
            GROUP_CONCAT(BC.name SEPARATOR "\n") as blockedConnections,
            GROUP_CONCAT(BC.name SEPARATOR "\n") as disableConsentConnections,
            GROUP_CONCAT(MD.key, '|SPLIT|',MD.value SEPARATOR "\n") as metadata,
            GROUP_CONCAT(U.userid SEPARATOR "\n") as users,
            'new'
FROM        janus__connectionRevision AS CV

-- Get allowed connections
LEFT JOIN   janus__allowedConnection AS ACR
    ON      ACR.connectionRevisionId = CV.id
LEFT JOIN   janus__connection AS AC
    ON      AC.id = ACR.remoteeid

-- Get blocked connections
LEFT JOIN   janus__blockedConnection AS BCR
    ON      BCR.connectionRevisionId = CV.id
LEFT JOIN   janus__connection AS BC
    ON      BC.id = BCR.remoteeid

-- Get disabled consent
LEFT JOIN   janus__disableConsent AS DCCR
    ON      DCCR.connectionRevisionId = CV.id
LEFT JOIN   janus__connection AS DCC
    ON      DCC.id = DCCR.remoteeid

-- Get metadata
LEFT JOIN   janus__metadata AS MD
    ON      MD.connectionRevisionId = CV.id

-- Get users
LEFT JOIN   janus__hasConnection AS UCR
    ON      UCR.eid = CV.eid
LEFT JOIN   janus__user AS U
    ON      U.uid = UCR.uid

WHERE   CV.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__connectionRevision
    WHERE       eid = CV.eid
)
GROUP BY CV.id
ORDER BY  CV.eid