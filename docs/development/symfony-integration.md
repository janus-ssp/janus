# Symfony integration

With the introduction of the [Doctrine 2 ORM](http://www.doctrine-project.org/) a start was made to integrate parts of the [Symfony 2 framework](http://symfony.com/). 

The first step was to introduce an AppKernel with a Janus CoreBunde for Janus dependencies, a SSPIntegrationBundle for integration with [SimpleSamlPhp](https://simplesamlphp.org/) and various vendor bundles like the [DoctrineBundle](https://github.com/doctrine/DoctrineBundle). This also introduced a few new directories:
- `app`: The Symfony application and config
- `src`: The new domain and Symfony bundle code

Since the first attempt to use Doctrine was done without using Symfony a separate Dependency injection container based on [Pimple](http://pimple.sensiolabs.org/) was added, part of this still remains

The second step was to add REST functionality which added another dir
- `web`: The Symfony app index file

The 3rd step was to combine all the existing configuration of Janus and the new Config which came with Doctrine and Symfony. The existing config was a php file located in the SimpleSamlPhp `config` dir. The Symfony config was in Yaml files located in the `app\config` dir (and the various bundles).
The chosen solution was to adapt the Symfony config style which meant that the existing Janus configuration had to be converted to Yaml. This was also an ideal oppurtunity to change the format of some configuration to a more desired format. In some cases this was even demanded by symfony. All config will now be parsed by Symfony so a [config definition](https://github.com/janus-ssp/janus/blob/develop/src/Janus/ServiceRegistry/Bundle/CoreBundle/DependencyInjection/Configuration.php) had to be created. this configuration is currently quite basic but could be improved by adding more default values, validation rules etc.

Converting existing configurations from php to yaml can be automated by using the `bin/migrateConfig` command.


