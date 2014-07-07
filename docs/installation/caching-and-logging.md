Caching and logging
===================

Overriding the default cache and/or logs dir:

Janus needs two writable directories, one for cache and one for logs. You an either:

create writable dirs (or softlinks to them) at:

```sh
app/cache

app/logs
```

OR create the following dirs:

```
/var/cache/janus-ssp/janus
/var/logs/janus-ssp/janus
```

Note that both dirs need exist and be writable for both apache as well as the command line user
(which executes the database migrations).
