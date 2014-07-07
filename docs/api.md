Simple RPC API to get data from JANUS

# Introduction

This simple RPC API enables users to retrive data from JANUS, to be used in other systems.

# Details

To call the API you need to make a GET request to the API URL:

http://janusdomain.com/simplesaml/module.php/janus/service/rest/

Required parameters for all requests:
- method

Required parameters for methods that require signing:
- janus_key
- janus_sig

Response
* If a request is successful a 200 status code is returned and the result json encoded.
* If the method requested or some parameters is missing a 400 status code is returned
* If the request for some reason can not be handled by JANSU a 500 status code is returned.

## Signing Requests
Let's presume that our shared secret is DEADBEEF. To sign a request, you need to:

Sort your parameters by key name, so that:
    ``yxz=foo feg=bar abc=baz``

becomes:
    ``abc=baz feg=bar yxz=foo``

* Construct a string with all key/value pairs concatenated together:
    ``abcbazfegbaryxzfoo``

* Concatenate the previous result onto your shared secret:
    ``DEADBEEFabcbazfegbaryxzfoo``

* Calculate the SHA-512 hash of this string:
    ``sha512('DEADBEEFabcbazfegbaryxzfoo')`` -> ``75178b3c27252027ae97b9a5eb36ce41``

We now use this result, ``75178b3c27252027ae97b9a5eb36ce41`` as our janus_sig parameter.
**Sample code**
```phph
    $secret = 'SECRET';
    $values = array(
        'janus_key' => 'USERNAME',
        'method' => 'getEntity',
        'entityid' => 'http://example.com',
    );

    ksort($values);
    $concat_string = '';
    foreach($values AS $key => $value) {
        $concat_string .= $key . $value;
    }
    $prepend_secret = $secret . $concat_string;
    $hash_string = hash('sha512', $prepend_secret);
    $query = http_build_query($values);
    $requestURL = 'http://janus-dev.test.wayf.dk/module.php/janus/services/rest/?'.$query.'&janus_sig=' .$hash_string;
```

## Methods
### echo

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>No</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**string**</td><td>optional</td></tr>
</table>

Returns the parameter string or if obmitted the string "JANUS". This method can be used to see if JANUS is alive.

### arp

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**entityid**</td><td>required</td></tr>
  <tr><td>**revision**</td><td>optional</td></tr>
</table>

Returns the ARP for the parsed entity

### getUser

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**userid**</td><td>required</td></tr>
</table>

Return data about the user

### getEntity

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**entityid**</td><td>required</td></tr>
  <tr><td>**revision**</td><td>loptional</td></tr>
</table>

Return data about the entity

### getMetadata

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**entityid**</td><td>required</td></tr>
  <tr><td>**revision**</td><td>optional</td></tr>
  <tr><td>**keys**</td><td>optional</td></tr>
</table>

Return metadata for the entity.

Note that if you do not pass along any **keys** (or properties) very little will be returned.
You must specify the metadata you want to see, for instance to get the SSO Binding, Location and name of an sp:

    keys=SingleSignOnService:0:Binding,SingleSignOnService:0:Location,name:en

### isConnectionAllowed

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**spentityid**</td><td>required</td></tr>
  <tr><td>**idpentityid**</td><td>required</td></tr>
  <tr><td>**sprevision**</td><td>loptional</td></tr>
  <tr><td>**idprevision**</td><td>loptional</td></tr>
</table>

Return true or false depending wether connection is allowed or not.

### getAllowedIdps

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**spentityid**</td><td>required</td></tr>
  <tr><td>**sprevision**</td><td>loptional</td></tr>
</table>

Return a list of all allowed Identity Provider entityIDs for a given Service Provider.

### getAllowedSps

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**idpentityid**</td><td>required</td></tr>
  <tr><td>**idprevision**</td><td>loptional</td></tr>
</table>

Return a list of all allowed Service Provider entityIDs for a given Identity Provider.

### getIdpList

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**keys**</td><td>optional</td></tr>
  <tr><td>**spentityid**</td><td>optional</td></tr>
</table>

Retrieves a list of all Identity Providers.

Note that if you do not pass along any **keys** (or properties) very little will be returned.
You must specify the metadata you want to see, for instance to get the SSO Binding, Location and name of an sp:

    keys=SingleSignOnService:0:Binding,SingleSignOnService:0:Location,name:en

### getSpList

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**keys**</td><td>optional</td></tr>
</table>

Retrieves a list of all Service Providers.

Note that if you do not pass along any **keys** (or properties) very little will be returned.
You must specify the metadata you want to see, for instance to get the ACS Binding, Location and name of an sp:
    keys=AssertionConsumerService:0:Binding,AssertionConsumerService:0:Location,name:en

### findIdentifiersByMetadata

<table>
  <tr><td>**Signing**</td><td></td></tr>
  <tr><td>Require signature</td><td>Yes</td></tr>
</table>

<table>
  <tr><td>Parameter name</td><td>status</td></tr>
  <tr><td>**key**</td><td>required</td></tr>
  <tr><td>**value**</td><td>required</td></tr>
  <tr><td>**userid**</td><td>required</td></tr>
</table>

Returns entityIds for those entities that have the metadata key=>value pair.
