#Obtaining Janus

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
