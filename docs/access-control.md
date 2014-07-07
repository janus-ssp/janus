Documentation for the access control option

*NOTE: this document is not complete*

# Defining access permissions

In JANUS a number of different elements are defined that you can control
access to. A complete list can be seen in the *Configuration options* section. The access
definitions are done using arrays. The syntax is as follows:
```yml
ELEMENT:
    default: BOOLEANVALUE
    STATE:
        role:
          - USERTYPE
```

Allowed values for ``STATE`` is the states defined in the ``workflowstates``
option. The allowed values for ``USERTYPE`` is the usertypes defined in the
``usertypes`` option and the value 'all'. Furthermore you can negate the
values by prefixing the value with a '-'.

If the element do not relate to a state, i.e.it is **global**, then the `role` parameter should replace
the ``STATE`` parameter. By setting the ``default`` parameter, you will control
access to all states you have not defined access for and for usertypes not set.

You can define multible instances of the ``STATE`` parameter, one for each defined state in  ``workflowstates``. This gives you very fine grained access control opportunities.

# Configuration options

| **Name** | **Global** | **Description** |
|----------|------------|-----------------|
| addearp | false | Add ARP |
| addmetadata | false | Add metadata |
| addsubscriptions | true | Add subscriptions |
| admintab | true | Adminitsartion tab |
| adminusertab | true | Adminitsartion users tab |
| allentities | true | Access to all entities |
| arpeditor | true | ARP editor tab |
| blockremoteentity | false | Block or unblock remote entities |
| changearp | false | Change ARP |
| changeentitytype | false | Allow the user to change the entity type of entities |
| changeworkflow | false | Change workflow state |
| createnewentity | true | Create new entity |
| deletemtadata | false | Delete metadata |
| deletesubscriptions | true | Delete subscriptions |
| disableconcent | false | Disable consent |
| editarp | false | Edit ARP |
| editsubscriptions | true | Edit subscriptions |
| entityhistory | false | See History tab |
| experimental | true | Experimental features |
| exportallentities | true | Export all entities |
| exportmetadata | false | Allow users to export metadata for entities |
| federationtab | true | Federation tab |
| importmetadata | false | Inport metadata |
| modifymetadata | false | Edit metadata |
| showsubscriptions | true | Show subscriptions |
| validatemetadata | false | Give access to validation tab |

If a permission is not set for a given user for a given state, the `default` permission is given.

If a permission for some reason or another is not set at all, no permission is given to any user.
