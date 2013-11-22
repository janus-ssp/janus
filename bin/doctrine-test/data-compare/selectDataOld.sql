SELECT      E.entityid,
            E.revisionid,
            E.state,
            E.type,
            E.expiration,
            E.metadataurl,
            E.metadata_valid_until,
            E.metadata_cache_until,
            E.allowedall,
            E.manipulation,
            E.user,
            E.created,
            E.ip,
            E.parent,
            E.revisionnote,
            E.active,
            ARP.attributes as arpAttributes,
            GROUP_CONCAT(AE.entityid SEPARATOR "\n") as allowedConnections,
            GROUP_CONCAT(BE.entityid SEPARATOR "\n") as blockedConnections,
            GROUP_CONCAT(BE.entityid SEPARATOR "\n") as disableConsentConnections,
            GROUP_CONCAT(MD.key, '|SPLIT|',MD.value SEPARATOR "\n") as metadata,
            GROUP_CONCAT(U.userid SEPARATOR "\n") as users,
            'old'
FROM        janus__entity AS E

-- Get arp
LEFT JOIN   janus__arp AS ARP
    ON      ARP.aid = E.arp

-- Get allowed entities
LEFT JOIN   janus__allowedEntity AS AER
    ON      AER.eid = E.eid
    AND     AER.revisionid = E.revisionid
LEFT JOIN   janus__entity AS AE
    ON      AE.eid = AER.remoteeid
    AND     AE.revisionid = (
        SELECT      MAX(revisionid)
        FROM        janus__entity
        WHERE       eid = AE.eid
    )

-- Get blocked entities
LEFT JOIN   janus__blockedEntity AS BER
    ON      BER.eid = E.eid
    AND     BER.revisionid = E.revisionid
LEFT JOIN   janus__entity AS BE
    ON      BE.eid = BER.remoteeid
    AND     BE.revisionid = (
        SELECT      MAX(revisionid)
        FROM        janus__entity
        WHERE       eid = BE.eid
    )

-- Get disabled consent
LEFT JOIN   janus__disableConsent AS DCER
    ON      DCER.eid = E.eid
    AND     DCER.revisionid = E.revisionid
LEFT JOIN   janus__entity AS DCE
    ON      DCE.entityid = DCER.remoteentityid
    AND     DCE.revisionid = (
        SELECT      MAX(revisionid)
        FROM        janus__entity
        WHERE       eid = DCE.eid
    )

-- Get metadata
LEFT JOIN   janus__metadata AS MD
    ON      MD.eid = E.eid
    AND     MD.revisionid = E.revisionid

-- Get users
LEFT JOIN   janus__hasEntity AS UER
    ON      UER.eid = E.eid
LEFT JOIN   janus__user AS U
    ON      U.uid = UER.uid

WHERE   E.revisionid = (
    SELECT      MAX(revisionid)
    FROM        janus__entity
    WHERE       eid = E.eid
)
GROUP BY E.eid, E.revisionid
ORDER BY  E.eid