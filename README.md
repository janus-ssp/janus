Master: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=master)](https://travis-ci.org/janus-ssp/janus)

Develop: [![Build Status](https://travis-ci.org/janus-ssp/janus.png?branch=develop)](https://travis-ci.org/janus-ssp/janus)

janus-ssp
=========

JANUS is a fully featured metadata registration administration module build on top of simpleSAMLphp.


See the file LICENCE for the licence conditions.


For discussing this project a mailinglist is available at https://list.surfnet.nl/mailman/listinfo/janus


Installation
============

JANUS is a module for simpleSAMLphp.

Note: Janus is developed on unix based systems and might not work on windows due to the use of softlinks (amongs others)

To set up JANUS you need to do the following:

  * Set up a working copy of simpleSAMLphp >= 1.7.0
  * Set up an authentication source
  * Download JANUS -> See Obtaining Janus
  * Set up database
  * Configure JANUS

For instructions on how to set up a working copy of simpleSAMLphp and how to
set up a authentication source, please refer to http://simplesamlphp.org/docs/

Then you should get the desired version of JANUS and install it as a module for
your simpleSAMLphp installation and copy the configuration file template to the
simpleSAMLphp configuration directory.

Next set up a working database and run the database migrations:
```
./bin/migrate
```

Note that the migrations can also upgrade an existing database. (always test this first). You should change the storageengine and
characterset to fit your needs. You can use another pefix for the table names
by editing the `prefix` option in the configuration file. (Note that the prefix option has been fixed since 1.17.0)

Set the parameter 'useridattr' to match the attribute you want
to make the connection between the user and the entities.

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

Janus as a Composer dependency
------------------------------------

While still a bit experimental. Janus can be now also installed using composer. This requires SimpleSamlPhp to be installed via Composer as well, add the following to your composer json:

```json
"require": {
    "janus-ssp/janus":"dev-master",
},
```

Note: Make sure SimpleSamlPhp is able to load janus from the vendor directory for example by softlinking it into
the modules directory

Note2: Correct the components softlink in the www/resources dir from:

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

Overriding the default cache and/or logs dir:

Janus needs two writable directories, one for cache and one for logs. You an either:

create writable dirs (or softlinks to them at:

```sh
app/cache

app/logs
```

OR configure paths to cache and logs dir like:

```php
'cache_dir' => '/var/cache/janus',

'log_dir' => '/var/logs/janus'
```

Note that both dirs need exist and be writable for both apache as well as the command line user
(which executes the database migrations).
