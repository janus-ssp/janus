
Installation
============

- [Caching and logging](caching-and-logging.md)
- [System Requirements](system-requirements.md)
- [Upgrade](upgrade.md)


* Set up a working copy of simpleSAMLphp >= 1.7.0
* Install Janus as a module for SSP
* Copy Janus example config (```app/config-dist/config_custom.yml```) to ```app/config``` dir.
* Customize your config:
    *  Set up an authentication source -> set the parameter 'useridattr' to match the attribute you want to make the connection between the user and the entities.
    * Create writable dirs for cache and logs  (see Caching and logging)
* Create a database
* Enter your database parameters in the ```app/config/parameters.yml``` file
* Run the database migrations:
```
./bin/migrate
```

*Note that the migrations can also upgrade an existing database. (always test this first).*

*Note: For instructions on how to set up a working copy of simpleSAMLphp and how to set up a authentication source, please refer to http://simplesamlphp.org/docs/*

Now you should have a working installation of JANUS. For a more detailed
introduction to JANUS and the configuration please go to
https://github.com/janus-ssp/janus/wiki/What-IsJANUS

More information can be found in the wiki at https://github.com/janus-ssp/janus/wiki

Obtaining Janus
===============
Obtaining a copy of Janus can be done in several ways.

The classic way: install from an (zip) archive from the Github releases page
----------------------------------------------------------------------------

Each version has a zip file available at github (which does not yet include the dependencies of janus)
The archive has to be extracted in a directory named 'janus' in the SimpleSamlPHP modules dir. After extracting, run composer (read on in the cloning part to see how this works).

Note that symlinking janus into the modules dir is not supported, except when you install both SimpleSamlPHP and janus via Composer.

Cloning the repository
----------------------

Janus can also be obtained directly from the git repository at GitHub
by cloning the project in the modules dir of SimpleSamlPhp, this makes updating easier. just run:

```sh
git clone https://github.com/janus-ssp/janus.git
```

Note: The git clone will not contain any dependencies, these have to be installed using the Composer dependency manager. (If you do not have composer go to: https://getcomposer.org/download/)

In the root of the janus project dir run:

```sh
composer.phar install
```

Or if you want to have development tools like PHPUnit installed as well run:

```sh
composer.phar install --dev
```
