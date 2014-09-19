#REST API

This document serves as reference and description for the new REST API for JANUS > 1.18.

# A connection

The primary use-case of the API is to manage JANUS connections. As such there is a single data-type called 'connection'
that contains all data for a JANUS connection. It is used both for API output as for input.

Here is an example of an IdP:

```json
{
    "updatedByUserName": "urn:collab:person:surfguest.nl:remold",
    "updatedFromIp": "145.97.21.120",
    "id": 43,
    "name": "urn:federation:HSZuydADFS",
    "revisionNr": 56,
    "state": "testaccepted",
    "type": "saml20-idp",
    "metadataUrl": "https:\/\/wayf.surfnet.nl\/federate\/metadata\/saml20\/urn%253Afederation%253AHSZuydADFS",
    "allowAllEntities": false,
    "manipulationCode": "",
    "parentRevisionNr": 55,
    "revisionNote": "Set to test status Migration (CXT-2923)",
    "isActive": true,
    "createdAtDate": "2013-09-27T15:19:36+0200",
    "updatedAtDate": "2013-09-27T15:19:36+0200",
    "metadata": {
        "certData": "MIIEVDCCAzygAwIBAgIJANm7yUGYaeG1MA0GCSqGSIb3DQEBBQUAMHkxCzAJBgNVBAYTAk5MMRAwDgYDVQQKEwdTVVJGbmV0MREwDwYDVQQLEwhTZXJ2aWNlczEZMBcGA1UEAxMQRmVkZXJhdGllIEJlaGVlcjEqMCgGCSqGSIb3DQEJARYbZmVkZXJhdGllLWJlaGVlckBzdXJmbmV0Lm5sMB4XDTA4MDYwNTE1MDgyMVoXDTIzMDYwMjE1MDgyMVoweTELMAkGA1UEBhMCTkwxEDAOBgNVBAoTB1NVUkZuZXQxETAPBgNVBAsTCFNlcnZpY2VzMRkwFwYDVQQDExBGZWRlcmF0aWUgQmVoZWVyMSowKAYJKoZIhvcNAQkBFhtmZWRlcmF0aWUtYmVoZWVyQHN1cmZuZXQubmwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC\/x+YuMaHyS3xeogfBB6hWrL4Frp+KzOuu4IixfhMHz3xIG5l7p2aNV8UrEXevOwMWCgMNxjfSLdZBgNhR14GBh2cVGCx9f\/wUtB86scmkP3PrRLoZWu\/EIY6MEbgET3D3tkdGuVejQwwhJTlK2xxWHtEdEL5abjYLveDg6Lb6z9odljFevylBMZO+5LwTjpa3+B+07oMZr2sV1yjsG2BEBwTFz4XZzJAabeK9UO836qhNptktjffoCNen33tNCjzqci4wzgQef3CNA\/Ef0tMKGotdldKC6FtHvXixmVY5RKUKIutm8sRwne8XYqrD54BAgXZQ0ZovxFbvGhA77YXxAgMBAAGjgd4wgdswHQYDVR0OBBYEFJNoYjIYUrDN\/h1+9BZYOTk7jQBNMIGrBgNVHSMEgaMwgaCAFJNoYjIYUrDN\/h1+9BZYOTk7jQBNoX2kezB5MQswCQYDVQQGEwJOTDEQMA4GA1UEChMHU1VSRm5ldDERMA8GA1UECxMIU2VydmljZXMxGTAXBgNVBAMTEEZlZGVyYXRpZSBCZWhlZXIxKjAoBgkqhkiG9w0BCQEWG2ZlZGVyYXRpZS1iZWhlZXJAc3VyZm5ldC5ubIIJANm7yUGYaeG1MAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADggEBAI4IxrYPwwjJD9gO1Vzt8ByeQaRe+V0Mv5Ox9RlcXV33WX8Ny8hqUS4\/kjs9v7JOuOw7TRop\/4QJIAv\/LEXH9B+hQ96zdLGMCcHI2crWF8l0yZ\/DtgkpdlcyS7dNbjLtedtmgrOMSQubLE02tqoSUR491mQbRuXD49+kJsHXZH8I1YZqOShzPZ7+ksvnBd64txhef8OBlCzEelT60nOC3Jm8k3i0HwPcCYfDrh6+MJfC2dvfgktAcyu8rm1Q\/ZelxaaXok17wUKgD8nDrVCOfTND1RCGcqJ3YVjYDhBrMdK+5NSuC5KOJUpVZbKgTOilnOM7B\/Os8HJCfxLkDyGV\/oQ=\t",
        "coin": {
            "guest_qualifier": "None",
            "hidden": true,
            "institution_id": "HSZUYD"
        },
        "contacts": [
            {
                "contactType": "technical",
                "emailAddress": "aai-beheer@surfnet.nl",
                "givenName": "AAI Beheer",
                "surName": "AAI Beheer"
            },
            {
                "contactType": "administrative",
                "emailAddress": "federatie-beheer@surfnet.nl",
                "givenName": "SURFfederatie Beheer",
                "surName": "SURFfederatie Beheer"
            },
            {
                "contactType": "technical",
                "emailAddress": "federatie-beheer@surfnet.nl",
                "givenName": "SURFfederatie Beheer",
                "surName": "SURFfederatie Beheer"
            }
        ],
        "description": {
            "en": "Zuyd University",
            "nl": "Zuyd Hogeschool"
        },
        "displayName": {
            "en": "Zuyd University (deprecated)",
            "nl": "Zuyd Hogeschool (deprecated)"
        },
        "keywords": {
            "en": "Zuyd University Hogeschool HBO",
            "nl": "Zuyd University Hogeschool HBO"
        },
        "logo": [
            {
                "height": "60",
                "url": "https:\/\/static.surfconext.nl\/media\/idp\/hszuyd.png",
                "width": "120"
            }
        ],
        "name": {
            "en": "Zuyd University (deprecated)",
            "nl": "Zuyd Hogeschool (deprecated)"
        },
        "OrganizationDisplayName": {
            "en": "Zuyd University",
            "nl": "Zuyd Hogeschool"
        },
        "OrganizationName": {
            "en": "Zuyd University",
            "nl": "Zuyd Hogeschool"
        },
        "OrganizationURL": {
            "en": "https:\/\/sflogin.hszuyd.nl\/adfs\/ls\/",
            "nl": "http:\/\/www.hszuyd.nl\/"
        },
        "redirect": {
            "sign": false
        },
        "SingleSignOnService": [
            {
                "Binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect",
                "Location": "https:\/\/wayf.surfnet.nl\/federate\/saml20\/urn%253Afederation%253AHSZuydADFS"
            }
        ]
    },
    "allowedConnections": [
        {
            "id": 2,
            "name": "https:\/\/teams.surfconext.nl\/shibboleth"
        },
        {
            "id": 9,
            "name": "https:\/\/filesender.surfnet.nl\/simplesaml\/module.php\/saml\/sp\/metadata.php\/default-sp"
        },
      ...
    ],
    "blockedConnections": [

    ],
    "disableConsentConnections": [
        {
            "id": 595,
            "name": "https:\/\/www.kiesactief.nl\/simplesaml\/module.php\/saml\/sp\/metadata.php\/surfnet"
        }
    ]
}
```

## Fields

| name | ? | type | Description | 
|------|---|------|-------------|
| id | readonly | integer | Internal ID for connection. |
| allowAllEntities | bool | Are all entities allowed to connect? | 
| allowedConnections | | array | <{id, name}> |
| blockedConnections | | array | <{id, name}> |
| createdAtDate | readonly | {id, name} |
| disableConsentConnections | | array | <{id, name}> |
| isActive | | boolean | 
| manipulationCode | | string | PHP Attribute manipulation code |
| metadata | | | Nested collection of metadata entries | 
| metadataUrl | |  string | |
| name | ? | string | Connection name. You probably want to fill in the EntityID here. |
| parentRevisionNr |  | integer | Revision nr where this revision was created from | 
| revisionNote |!? | string | What was the last / current change about? May be obligatory depending on the 'revision.notes.required' setting. |
| revisionNr | readonly | integer | Number of revisions this connection has had (starting with revision 0). |
| state | | string | Must be one of the configured workflow states, by default (in ascending order or stability): <ul><li>testaccepted</li><li>QApending</li><li>QAaccepted</li><li>prodpending</li><li>prodaccepted</li></ul> |
| type | ! | string | <span> Required field that specifies what type the connection is, by default one of: <ul><li>saml20-sp</li><li>saml20-idp</li><li>shib13-sp</li><li>shib13-idp</li></ul> |
| updatedAtDate | readonly | date | |
| updatedByUserName | readonly | string | Username of the user that last updated the connection. |
| updatedFromIp | readonly | string | IP address the connection was last updated from. |


## Metadata


### 1. Group
A nested group of related fields.

```json
"name": {
    "nl":"",
    "en":"",
}
```

### 2. Collection

```json
"logo": [
  { ... },
  { ... }
]
```

### 3. Key / Value

```json
"height":"96"
```

# List structure

A 'list' of connections has the following structure:

```json
{
    "connections": {
        "saml20-idp": {
            "17": {
                "id": 17,
                "...":"..."
            }
        }
    }
}
```

Note that the root element is "connections", below this is the type and per type a connection indexed by the id.

# Stop! Identify yourself

The new REST API currently uses **HTTP Basic** (though support for other Authentication mechanisms
like Digest or OAuth2 is in the works).

To create a user login as that user, go to your Dashboard, select User and enter a secret.
This secret will be used for the API.

**TODO insert pic here**

# Managing Connections

Here is a complete CURL example to get a list of all entities:

```bash
curl -u user:password https://serviceregistry.demo.openconext.org/janus/app.php/api/connections.json
```

If you want to 'pretty print' this you can use Pythons 'json.tool' like so:
```bash
curl \
     -u user:password \
     https://serviceregistry.demo.openconext.org/janus/app.php/api/connections.json | python -mjson.tool
```

Appending '.json' to the URL can be used as a 'override' for the HTTP 'Accept' header,
specifying which Content Type you want (currently we only support "application/json").
Unfortunately the same doesn't currently work for input, for which you should always send the HTTP "Content-Type" header.

## List all connections

    GET /connections

```bash
curl -u user:password https://serviceregistry.demo.openconext.org/janus/app.php/api/connections.json
```

## Create a new connection

```
POST /connection/{id}
Content-Type: application/json

{"type":"saml20-idp", "name":"API TEST", "revisionNote":"Test new REST API"}
```

```bash
curl \
      -v \
      -X POST \
      -H "Content-Type: application/json"
      -u user:password \
      https://serviceregistry.demo.openconext.org/janus/app.php/api/connections
```

## List a single connection by id

```
GET /connection/{id}
```

```bash
curl -u user:password https://serviceregistry.demo.openconext.org/janus/app.php/api/connections/1.json
```

## Update a connection

```
PUT /connection/{id}
```

```bash
curl \
     -v \
     -X DELETE \
     -u user:password \
     https://serviceregistry.demo.openconext.org/janus/app.php/api/connections/1.json
```

## Remove a connection

```
DELETE /connection/{id}
```

```bash
curl \
     -v \
     -X DELETE \
     -u user:password \
     https://serviceregistry.demo.openconext.org/janus/app.php/api/connections/1.json
```

# Snapshots

The snapshot API is a helpful feature that allows you to quickly save and restore a JANUS configuration.
It's primary use-case is in backing up and restoring back-ups before and after functional tests.

By default snapshots are written out to and read from /tmp/janus/snapshots as SQL files.
Note that you can't actually get these SQL files through the API.

## List all snapshots

```
GET /snapshots
```

```bash

```


## Create a new snapshot

```
POST /snapshots
```

```bash

```


## List a snapshot information

```
GET /snapshots/{id}.json
```

```bash

```

## Remove a snapshot

```
DELETE /snapshots/{id}
```

```bash

```


## Restore a snapshot

```
POST /snapshots/{id}/restores
```

```bash

```

# Finally
Don't forget to have fun!