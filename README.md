Master: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=master)](https://travis-ci.org/janus-ssp/janus) Develop: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=develop)](https://travis-ci.org/janus-ssp/janus)

JANUS is a fully featured metadata registration administration module build on top of simpleSAMLphp. 

*Note that Janus is being developed on Linux/OS X and might not work on windows due to the use of softlinks (amongst others).*

For discussing this project a mailinglist is available at https://list.surfnet.nl/mailman/listinfo/janus. Issues can be created at: https://github.com/janus-ssp/janus/issues.

Installation
============
To set up JANUS you need to do the following:

- Set up a working copy of simpleSAMLphp >= 1.7.0 and set up an set up a authentication source. For more info see the [docs](http://simplesamlphp.org/docs/). 
- EITHER Get Janus source code from a (gzip) archive from the Github releases page
  - Extract release from [Github](https://github.com/janus-ssp/janus/releases) into the SimpleSamlPhp ``modules`` dir. *Note: that symlinking janus into the modules dir is not supported, except when you install both SimpleSamlPHP and janus via Composer.*
- OR get Janus source by cloning the GIT repository
  - Run: ``sh git clone https://github.com/janus-ssp/janus.git`` in the the SimpleSamlPhp ``modules`` dir.
  - Go to the ``janus`` directory and install dependencies: ``sh composer.phar install --no-dev``
- OR Add Janus as a Composer dependency, this requires SimpleSamlPhp to be installed via Composer as well.
  - Run: ``sh composer require janus-ssp/janus``
  - Run composer ``sh composer.phar install --no-dev``
  - Make sure SimpleSamlPhp is able to load janus from the vendor directory for example by softlinking it into the modules directory
  - Correct the components softlink in the www/resources dir from: ``sh ../../components``, to: ``sh ../../../../../components ``

   For a working implementation of Janus as a dependency see:
   https://github.com/OpenConext/OpenConext-serviceregistry/blob/develop/composer.json
- Create a database
- Go to the install page: ``{urltoyoursimplesamlphp}/module.php/janus/install/``
- Copy the generated configuration file to the simpleSAMLphp configuration directory
- Optionally do some more configuration see: [#Configuration](#configuration)

Now you should have a working installation of JANUS. For a more detailed introduction to JANUS and the configuration please go to: https://github.com/janus-ssp/janus/wiki/What-IsJANUS

More information can be found in the wiki at https://github.com/janus-ssp/janus/wiki

Configuration
=============

Authentication configuration
----------------------------

Set the parameter 'useridattr' to match the attribute you want
to make the connection between the user and the entities.

Database configuration
----------------------
By default janus prefixes all tables with ``janus__``. This can be changdd by editing the `prefix` option in the configuration file. *Note that the prefix option has been fixed since 1.17.0*

Cache configuration
-------------------

Janus expects the following dirs to be present writable: ``/var/cache/janus`` and ``/var/logs/janus``. If you want to change this you can configure paths to cache and logs dir like:

```php
'cache_dir' => '/my/own/cachedir',

'log_dir' => '/my/own/logs/dir'
```

Note that to able to upgrade the database the command line user also has to have write permissions to these directories.

Upgrading
=========
- Depending on how you installed Janus get a newer version of the sourcecode, if you installed via git or composer run composer.
- Run the database migrations: ``sh ./bin/migrate`` *(make sure to do this on a test database first and/or make backups).* 

Developer info
==============

Creating a release
------------------

Janus has built in support for creating a release. The created releases are meant to create a version of Janus which works as a plugin for SimpleSamlPhp

Creating a release is as simple as calling
``sh ./RMT release ``

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
