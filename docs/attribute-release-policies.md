# Attribute Release Policies

You may with to curate the attributes that Identity Providers emit or that Service Providers consume. The Attribute Release Policy feature allows you to specify which attributes may be released to or from an IdP or SP.

## Specifying Attributes

In the configuration file, you can define what attributes, that can be used for generation Attribute Release Policies. The attributes are defined in the option `attributes`. The syntax is as follows:
```yml
attributes:
    LABEL1:
        name: ATTRIBUTE_NAME1
        specify_values: BOOLEAN
```
The LABEL is how the attribute is displayed, for example "Common Name".

The ATTRIBUTE_NAME is the actual name of the attribute, for example ``urn:mace:dir:attribute-def:cn`` (SAML1) or ``urn:oid:2.5.4.3`` (SAML2)

The specify_values setting controls whether you want to require the user to specify allowed values for the attribute.
For exmple the ``urn:oid:1.3.6.1.4.1.5923.1.5.1.1`` or ``isMemberOf`` attribute, one might specify that the membership is only released if it has the value of 'federation-users' or 'federation-admins', effectively filtering out all other (IdP supplied) memberships.

**NOTE** that you can ONLY get at the specified values via the API, these are not supported in the metadata export.
