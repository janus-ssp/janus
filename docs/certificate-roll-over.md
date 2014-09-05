JANUS supports signing certificates rollover, to make it easier to change certificates on a production environment.

By defining a metadata field called `certData2` (and optionally `certData3`), JANUS will export both the certificate defined in `certData` and `certData2` (and optionally `certData3`) as signing certificates. Note that encryption certificates are not imported by default.

This means that metadata exported for the given entity is exported with two distinct certificates, making it easy and simple to change certificates for a given entity in a production environment.