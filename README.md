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

Installing Janus
================

* Set up SimpleSamlSsp
* Install Janus as a module for SSP
* Copy Janus example config (```app/config-dist/config_custom.yml```) to ```app/config``` dir.
* Customize your config:
** Set the parameter 'useridattr' to match the attribute you want to make the connection between the user and the entities.
** Create writable dirs for cache and logs  (see Caching and logging)
* Create a database
* Enter your database parameters in the ```app/config/parameters.yml``` file
* Run the database migrations
```
./bin/migrate
```

*Note that the migrations can also upgrade an existing database. (always test this first).*

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

```php
'cache_dir' => '/var/cache/janus-ssp/janus',

'log_dir' => '/var/logs/janus-ssp/janus'
```

Note that both dirs need exist and be writable for both apache as well as the command line user
(which executes the database migrations).

-
-Developer info
-==============
-
-Creating a release
-------------------
-
-Janus has built in support for creating a release. The created releases are meant to create a version of Janus which works as a plugin for SimpleSamlPhp
-
-Creating a release is as simple as calling
-```sh
-cd bin
-sh ./RMT release
-```
-
-The tool will then asked a series of questions and create a release in the releases dir.
-
-The tool behaves differently depending on which branch it is called from. While the tool is meant to make an official release from master in the first place it's also possible to make releases of other branches.
-
-When making a release from master the following happens:
-- Check if working copy is clean
-- Check if unittests can be runned succesfully
-- Update the changelog
-- Create a tag
-- Push tag to github
-- Create an archive in the releases dir suffixed with the tag name
-- Create an archive in the releases dir suffixed with the tag name
-
-When making a release from a branch other than master the following happens:
-- Check if working copy is clean
-- Check if unittests can be runned succesfully
-- Update the changelog
-- Create an archive in the releases dir suffixed with the branch name and commit hash
