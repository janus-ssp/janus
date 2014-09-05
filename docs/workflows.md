# Description of workflows in JANUS

# Workflows

JANUS uses workflow to control the evolution of an entity. The different
states an entiy can be in is defined by the option `workflowstates` in the
configuration file. The syntax is as following:

```yml
STATEKEY:
    name: STATENAME
    description: STATEDESCRIPTION
    isDeployable: BOOLEAN
```

The `STATEKEY` is user defined and is only used as an internal key. The
`STATENAME` is a human readable name for the state and is the information
presented to the users. The `STATEDESCRIPTION` is a description of the meaning of the state. This information is also displayed to the user. The isDeployable specifies whether entities located in  this state should be returned when using the REST interface to JANUS.

The state transitions is defined in the option `workflow_states`. The syntax
for defining state transitions is as following:

```yml
STARTSTATE:
    ENDSTATE:
        role:
    	    USERTYPE
    ENDSTATE:
    	role:
    	    USERTYPE:
```

You can define multiple endstates to allow for different workflows. `USERTYPE` uses the same syntax as for access permissions.

# Configuration options

| Name | Value | Description |
|------|-------|-------------|
| workflowstates | *array* | Describes the different states in JANUS |
| workflow | *array* | Describes the transissions allowed between the states an who are allowed to make these transissions. |
| workflowstate.default | *string* | Name of the state that all newly created entities should be put in. |