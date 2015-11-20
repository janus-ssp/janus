#Metadata

[Export metadata](metadata/export.md)

# Defining metadata fields

In JANUS there is a distinction between metadata and metadata fields. Metadata fields are configuration, describing the different types of metadata that can be configured for the entities and metadata is the actual values set for the individual entities.

This document will explain how to  configure metadata fields in the JANUS configuration file.

Defining a metadata field is done using a yml aray:
```yml
METADATAFIELDNAME:
    OPTION1: OPTIONVALUE1
    OPTION2: OPTIONVALUE2
    ...
```

## Options

The following options can be used when defining metadata fields. See [Examples](Metadata#Examples) section for examples or the example config file.

|*Option*|*Value*|*Required*|
|--------|-------|----------|
| [type](#type) |string (text, select, boolean)|Yes|
|[select_values](Metadata#select_values)|array|Yes (if type is `select`)|
|[order](Metadata#order)|int|No|
|[default](Metadata#default)|string, boolean|No|
|[required](Metadata#required)|boolean|No|
|[validate](Metadata#validate)|string|No|
|[supported](Metadata#supported)|array|SNo|
|[default_allow](Metadata#default_allow)|boolean|No|

----
## type

| Type | string |
|--------|--------|
| *Required* | Yes |

Describes the type of metadata. The different types will be displayed with different input types in the edit connection view. The following types are defined:

- `text` - Will be rendered as a text field.
- `select` - Will be rendered as a select box.
- `boolean` - Will be rendered as a checkbox.

----
## select_values

| Type | array | 
|---------|-------|
| *Required* | yes, is type is `select` |

Describes all possible values to be selected in the select box. The array is a simple array with string values. The values are used both as key and value in the select box.

----
## order

| Type | int |
|------|-----|
| *Required* | No |

Describes the sort order in which the different fields are displayed, both for existing metadata and for metadata fields. If two metadata fields have the same order, the the metadata field defined first takes precedents.

----
## default

| Type | string, boolean | 
|------|-----------------|
| *Required* | No |

The default values the field are populated with when forst selected. The type is string when type is `text`, `file`, `select` and boolean when type is `boolean`. If `default` is not set, then the field will not be populated with predefined text when created.

----
## required

| Type | boolean  | 
|------|----------|
| *Required* | No |

Describes if a field is required for the entity. All fields marked with `required` are automatically created when new entities are created and fields marked with `required` can not be deleted.

**NOTE:** If the `required` option is used with the `supported` option, than all expanded metadata fields are marked required.

----
## validate

| Type | ? |
|------|---|
| *Allowed value(s)* | ? |
| *Required* | No |

Name of function that should validate input. See [this section](#custom-validation) for more details.

----
## supported

| Type | array  | 
|------|--------|
| *Required* | No |

Expand the metadata field to multiple fields, based on the content of the array. The array can contain both int and string values.

The field name must contain a `#` for the `supported` option to take effect. The `#` character in the field name is substituted with the values in the array to produce identical fields, where only the name differs.

REMEMBER to add a `:` if you want to create multiple of the same field, but with different subkeys. 

NOTE that the `required` field SHOULD NOT be used with `supported`, since all expanded fields are then required.

----
## default_allow

| Type | boolean | 
|------|---------|
| *Required* | No |

Describes whether or not the dafault value for the field is a valid value for the field.

----
# Examples

Below is an example of how to define a new metadata field using the most common options.
```yml
    metadatafieldname:
        type: text
        order: 130,
        default: CHANGE ME
        default_allowed: false,
        required: false,
```

Two examples of the use of `supported`:
```yml
    AssertionConsumerService:#:Location
        type: text
        order: 140
        default: CHANGE ME
        supported: [0,1,2,3,4]
    
    name:#
        type: text
        order: 145
        default: CHANGE ME
        supported: [en, da]
 ```
   
The above example will give 5 entries for adding AssertionConsumerService endpoints.

#Custom validation

JANUS offers the possibility to define your own custom validation functions for your metadata fields. Just write your function and add the function name to the config file.

You can define your function in the `Metadata.php` file in the `<PATH TO JANUS>/lib/Validation` directory. An example of a function can be seen here:
```php
'leneq40' => array(
    'code' => '
        if(strlen($value) == 40) return true; 
        else return false;
    ',
),
```
The above function checks that the entered value is no longer that 40 characters.

Your function should always return `true` or `false`. The value of the metadata field is always accessible in the `$value` variable.
