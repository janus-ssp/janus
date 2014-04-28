Master: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=master)](https://travis-ci.org/janus-ssp/janus)

Develop: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=develop)](https://travis-ci.org/janus-ssp/janus)

janus-ssp
=========

JANUS is a fully featured metadata registration administration module build on top of simpleSAMLphp. Note that Janus is being developed on linux/osx and might not work on windows due to the use of softlinks (amongst others).

For discussing this project a mailinglist is available at https://list.surfnet.nl/mailman/listinfo/janus

Installation
============
To set up JANUS you need to do the following:

* [Set up a working copy of simpleSAMLphp >= 1.7.0 and set up an set up a authentication source](http://simplesamlphp.org/docs/). For instructions on how to set up a working copy of simpleSAMLphp and how to * [Obtain JANUS](#Obtaining Janus)
* Set up database
* Configure JANUS
- Create a working database
 - Go to the install page: ``{urltoyoursimplesamlphp}/module.php/janus/install/``
 - Copy the configuration file template to the simpleSAMLphp configuration directory
 - Configure caching dirs see: [Cache configuration](#Cache configuration)
 - Create/Configure caching dirs see: [Cache configuration](#Cache configuration)
 - Configure caching dirs see: [Cache configuration](#Cache configuratio)


Now you should have a working installation of JANUS. For a more detailed
introduction to JANUS and the configuration please go to
https://github.com/janus-ssp/janus/wiki/What-IsJANUS

More information can be found in the wiki at https://github.com/janus-ssp/janus/wiki

Obtaining Janus
===============

From a (gzip) archive from the Github releases page
-----------------------------------------------------------------------------
- Extract release from [Github](https://github.com/janus-ssp/janus/releases) into the SimpleSamlPhp ``modules`` dir.

Note: that symlinking janus into the modules dir is not supported, except when you install both SimpleSamlPHP and janus via Composer.

Using GIT
---------------------------------
- Clone Janus by running: ``sh git clone https://github.com/janus-ssp/janus.git`` in the the SimpleSamlPhp ``modules`` dir.
- Go to the ``janus`` directory and install dependencies: ``sh composer.phar install --no-dev``

As a Composer dependency
--------------------------------------
Janus itself can be now also installed using composer. This requires SimpleSamlPhp to be installed via Composer as well, 

- add the following to your composer json: 

```json
"require": {
    "janus-ssp/janus":"dev-master",
},
```
- run composer ``sh composer.phar install --no-dev``
- Make sure SimpleSamlPhp is able to load janus from the vendor directory for example by softlinking it into
the modules directory
- Correct the components softlink in the www/resources dir from:

```sh
../../components
```

to:

```sh
../../../../../components
```

For a working implementation of Janus as a dependency see:
https://github.com/OpenConext/OpenConext-serviceregistry/blob/develop/composer.json


Configuration
=============

Authentication configuration
----------------------------

Set the parameter 'useridattr' to match the attribute you want
to make the connection between the user and the entities.

Database configuration
----------------------

Create a database and configure the credentials or let the installer do this for you. 

You should change the storageengine and
characterset to fit your needs. You can use another pefix for the table names
by editing the `prefix` option in the configuration file. (Note that the prefix option has been fixed since 1.17.0)

Updating
========

- Run the database migrations: ``sh ./bin/migrate``

Note that the migrations can also upgrade an existing database. (always test this first). 


Cache configuration
-------------------

Janus expects the following dirs to be present writable: ``/var/cache/janus`` and ``/var/logs/janus``. If you want to change this you can configure paths to cache and logs dir like:

```php
'cache_dir' => '/my/own/cachedir',

'log_dir' => '/my/own/logs/dir'
```

Note that to able to upgrade the database the command line user also has to have write permissions to these directories.

Creating a release
==================

Janus has built in support for creating a release. The created releases are meant to create a version of Janus which works as a plugin for SimpleSamlPhp

Creating a release is as simple as calling
```sh
./RMT release
```

The tool will then asked a series of questions and create a release in the releases dir.

The tool behaves differently depending on which branch it is called from. While the tool is meant to make an official release from master in the first place it's also possible to make releases of other branches.

When making a release from master the following happens:
- Check if working copy is clean
- Check if unittests can be runned succesfully
- Update the changelog
- Create a tag
- Push tag to github
- Create an archive in the releases dir suffixed with the tag name
- Create an archive in the releases dir suffixed with the tag name

When making a release from a branch other than master the following happens:
- Check if working copy is clean
- Check if unittests can be runned succesfully
- Update the changelog
- Create an archive in the releases dir suffixed with the branch name and commit hash

Licence
=======

See the file LICENCE for the licence conditions.
