This document serves as reference and description for the new REST API for JANUS > 1.18.

# A connection

The primary use-case of the API is to manage JANUS connections. As such there is a single data-type called 'connection'
that contains all data for a JANUS connection. It is used both for API output as for input.

Here is an example of an IdP:

```json
{
    "id": 9,
    "name": "https://serviceregistry.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp",
    "revision_note": "Entity created.",
    "revision_nr": 0,
    "state": "prodaccepted",
    "type": "saml20-sp",
    "updated_by_user_name": 1,
    "updated_from_ip": "172.18.5.1",
    "allow_all_entities": true,
    "allowed_connections": [
        {
            "id": 1,
            "name": "https://engine.demo.openconext.org/authentication/sp/metadata"
        },
        {
            "id": 3,
            "name": "https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp"
        },
        {
            "id": 4,
            "name": "https://manage.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp"
        },
    ],
    "blocked_connections": [],
    "created_at_date": "2012-06-13T08:00:55+0200",
    "disable_consent_connections": [],
    "is_active": true,
    "metadata": {
        "AssertionConsumerService": [
            {
                "Binding": "urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST",
                "Location": "https://serviceregistry.demo.openconext.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp"
            }
        ],
        "NameIDFormat": "urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified",
        "NameIDFormats": [
            "urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified",
            "urn:oasis:names:tc:SAML:2.0:nameid-format:transient",
            "urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"
        ],
        "contacts": [
            {
                "contactType": "technical",
                "emailAddress": "support@openconext.org",
                "givenName": "Support",
                "surName": "OpenConext"
            },
            {
                "contactType": "support",
                "emailAddress": "support@openconext.org",
                "givenName": "Support",
                "surName": "OpenConext"
            },
            {
                "contactType": "administrative",
                "emailAddress": "support@openconext.org",
                "givenName": "Support",
                "surName": "OpenConext"
            }
        ],
        "description": {
            "en": "OpenConext Service Registry, register all SPs and IdPs here",
            "nl": "OpenConext Service Registry, register all SPs and IdPs here"
        },
        "displayName": {
            "en": "OpenConext ServiceRegistry",
            "nl": "OpenConext ServiceRegistry"
        },
        "logo": [
            {
                "height": "96",
                "url": "https://static.demo.openconext.org/media/conext_logo.png",
                "width": "96"
            }
        ],
        "name": {
            "en": "OpenConext ServiceRegistry",
            "nl": "OpenConext ServiceRegistry"
        },
        "redirect": {
            "sign": ""
        }
    }
}
```

## Fields

### id (readonly)(integer)
Internal ID for connection.

### name (!)(string)
Connection name.
You probably want to fill in the EntityID here.

### revision_note (!?)(string)
What was the last / current change about?
May be obligatory depending on the 'revision.notes.required' setting.

### revision_nr (readonly)(integer)
Number of revisions this connection has had (starting with revision 0).

### state (string)
Must be one of the configured workflow states, by default (in ascending order or stability):
* testaccepted
* QApending
* QAaccepted
* prodpending
* prodaccepted

### type (!)(string)
Required field that specifies what type the connection is, by default one of:
* saml20-sp
* saml20-idp
* shib13-sp
* shib13-idp

### updated_by_user_name (readonly)(string)
Username of the user that last updated the connection.

### updated_from_ip (readonly)(string)
IP address the connection was last updated from.

### allow_all_entities (bool)
Are all entities allowed to connect?

### allowed_connections (array<{id, name}>)
### blocked_connections (array<{id, name}>)
### created_at_date (readonly)({id, name})
### disable_consent_connections (array<{id, name}>)
### is_active (boolean)
### metadata

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
