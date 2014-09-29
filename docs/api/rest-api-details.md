## /api/connections ##

### `GET` /api/connections.{_format} ###

_List all connections, this includes both Service providers and Identity providers._

List all connections, this includes both Service providers and Identity providers.

#### Requirements ####

**_format**

  - Requirement: json


### `POST` /api/connections.{_format} ###

_Creates a new connection from the submitted data._

Creates a new connection from the submitted data.

#### Requirements ####

**_format**

  - Requirement: json

#### Parameters ####

name:

  * type: string
  * required: true
  * description: Name (or in SAML speak 'entityid')

state:

  * type: string
  * required: true
  * description: State (e.g. testaccepted, prodaccepted)

type:

  * type: string
  * required: true
  * description: Type (e.g. saml20-sp, saml20-idp)

expirationDate:

  * type: DateTime
  * required: false
  * description: Date / time the connection itself can be considered as being expired

metadataUrl:

  * type: string
  * required: false
  * description: Url to the metadata

metadataValidUntil:

  * type: string
  * required: false
  * description: Date / time until the metadata can be considered as valid

metadataCacheUntil:

  * type: DateTime
  * required: false
  * description: Date / time until when the metadata can be safely cached

manipulationCode:

  * type: string
  * required: false
  * description: PHP code which can be used to manipulate a request

revisionNote:

  * type: string
  * required: true
  * description: Note regarding this specific revision

notes:

  * type: string
  * required: false
  * description: General note

allowAllEntities:

  * type: boolean
  * required: false
  * description: Are all connections allowed to connection to this connection?

arpAttributes[]:

  * type: array of arrays
  * required: false
  * description: A list of attributes that will be will released to the Service Provider (Identity Provider only)

isActive:

  * type: boolean
  * required: false
  * description: Is the connection active?

metadata:

  * type: object (MetadataDto)
  * required: false
  * description: Nested metadata

allowedConnections:

  * type: array
  * required: false
  * description: Connection that are allowed to connect

blockedConnections:

  * type: array
  * required: false
  * description: Connections that are NOT allowed to connect

disableConsentConnections:

  * type: array
  * required: false
  * description: Connections for which no consent is required when connecting


## /api/connections/{id} ##

### `GET` /api/connections/{id}.{_format} ###

_Get the latest revision of a single connection._

Get the latest revision of a single connection.

#### Requirements ####

**_format**

  - Requirement: json
**id**

  - Type: int

#### Response ####

name:

  * type: string
  * description: Name (or in SAML speak 'entityid')

state:

  * type: string
  * description: State (e.g. testaccepted, prodaccepted)

type:

  * type: string
  * description: Type (e.g. saml20-sp, saml20-idp)

expirationDate:

  * type: DateTime
  * description: Date / time the connection itself can be considered as being expired

metadataUrl:

  * type: string
  * description: Url to the metadata

metadataValidUntil:

  * type: string
  * description: Date / time until the metadata can be considered as valid

metadataCacheUntil:

  * type: DateTime
  * description: Date / time until when the metadata can be safely cached

manipulationCode:

  * type: string
  * description: PHP code which can be used to manipulate a request

revisionNote:

  * type: string
  * description: Note regarding this specific revision

notes:

  * type: string
  * description: General note

id:

  * type: integer
  * description: Unique Identifier

revisionNr:

  * type: integer
  * description: Revision number

allowAllEntities:

  * type: boolean
  * description: Are all connections allowed to connection to this connection?

arpAttributes[]:

  * type: array of arrays
  * description: A list of attributes that will be will released to the Service Provider (Identity Provider only)

parentRevisionNr:

  * type: integer
  * description: Number of the Revision this revision was based on

isActive:

  * type: boolean
  * description: Is the connection active?

createdAtDate:

  * type: DateTime
  * description: Date / time of creation

updatedAtDate:

  * type: DateTime
  * description: Date / time of last update

metadata:

  * type: object (MetadataDto)
  * description: Nested metadata

allowedConnections:

  * type: array
  * description: Connection that are allowed to connect

blockedConnections:

  * type: array
  * description: Connections that are NOT allowed to connect

disableConsentConnections:

  * type: array
  * description: Connections for which no consent is required when connecting


### `PUT` /api/connections/{id}.{_format} ###

_Update existing connection from the submitted data or create a new connection at a specific location._

Update existing connection from the submitted data or create a new connection at a specific location.

#### Requirements ####

**_format**

  - Requirement: json
**id**

  - Type: int

#### Parameters ####

name:

  * type: string
  * required: false
  * description: Name (or in SAML speak 'entityid')

state:

  * type: string
  * required: false
  * description: State (e.g. testaccepted, prodaccepted)

type:

  * type: string
  * required: false
  * description: Type (e.g. saml20-sp, saml20-idp)

expirationDate:

  * type: DateTime
  * required: false
  * description: Date / time the connection itself can be considered as being expired

metadataUrl:

  * type: string
  * required: false
  * description: Url to the metadata

metadataValidUntil:

  * type: string
  * required: false
  * description: Date / time until the metadata can be considered as valid

metadataCacheUntil:

  * type: DateTime
  * required: false
  * description: Date / time until when the metadata can be safely cached

manipulationCode:

  * type: string
  * required: false
  * description: PHP code which can be used to manipulate a request

revisionNote:

  * type: string
  * required: false
  * description: Note regarding this specific revision

notes:

  * type: string
  * required: false
  * description: General note

allowAllEntities:

  * type: boolean
  * required: false
  * description: Are all connections allowed to connection to this connection?

arpAttributes[]:

  * type: array of arrays
  * required: false
  * description: A list of attributes that will be will released to the Service Provider (Identity Provider only)

isActive:

  * type: boolean
  * required: false
  * description: Is the connection active?

metadata:

  * type: object (MetadataDto)
  * required: false
  * description: Nested metadata

allowedConnections:

  * type: array
  * required: false
  * description: Connection that are allowed to connect

blockedConnections:

  * type: array
  * required: false
  * description: Connections that are NOT allowed to connect

disableConsentConnections:

  * type: array
  * required: false
  * description: Connections for which no consent is required when connecting


### `DELETE` /api/connections/{id}.{_format} ###

_Removes a connection._

Removes a connection.

#### Requirements ####

**_format**

  - Requirement: json
**id**

  - Type: integer
  - Description: id of the connection to be deleted.


## /api/snapshots ##

### `GET` /api/snapshots.{_format} ###

_List all snapshots._

List all snapshots.

#### Requirements ####

**_format**

  - Requirement: json


### `POST` /api/snapshots.{_format} ###

_Create a new snapshot_

Create a new snapshot

#### Requirements ####

**_format**

  - Requirement: json


## /api/snapshots/{id} ##

### `GET` /api/snapshots/{id}.{_format} ###

_Returns a 200 if a given snapshot exists or a 404 if it does not._

Returns a 200 if a given snapshot exists or a 404 if it does not.

#### Requirements ####

**_format**

  - Requirement: json
**id**

  - Type: int


### `DELETE` /api/snapshots/{id}.{_format} ###

_Delete a snapshot._

Delete a snapshot.

#### Requirements ####

**_format**

  - Requirement: json
**id**

  - Type: int


## /api/snapshots/{id}/restores ##

### `POST` /api/snapshots/{id}/restores.{_format} ###

_Restore a snapshot._

Restore a snapshot.

#### Requirements ####

**_format**

  - Requirement: json
**id**

  - Type: int
