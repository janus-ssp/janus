Installation
============

- [System Requirements](system-requirements.md)
- [Upgrade](upgrade.md)

* Set up a working copy of simpleSAMLphp >= 1.15.0
* Install Janus as a module for SSP (see: [Obtaining Janus](obtain.md))
  ```Alias /janus </some/path>/simplesamlphp/modules/janus/web```
* Copy Janus example config (```app/config-dist/*.yml```) to ```app/config``` dir.
* Customize your config:
    *  Set up an authentication source -> set the parameter 'useridattr' to match the attribute you want to make the connection between the user and the entities.
    * Create writable dirs for cache and logs  (see [Caching and logging](caching-and-logging.md))
* Create a database
* Enter your database parameters in the ```app/config/parameters.yml``` file
* Run the database migrations (also for a fresh installation):
```
./bin/migrate.sh
```

*Note that the migrations can also upgrade an existing database. (always test this first).*

*Note: For instructions on how to set up a working copy of simpleSAMLphp and how to set up an authentication source, please refer to http://simplesamlphp.org/docs/*

Now you should have a working installation of JANUS. For a more detailed introduction to JANUS and the configuration please read the [Quickstart](../quickstart.md)
