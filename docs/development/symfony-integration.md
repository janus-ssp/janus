# Symfony integration

## Past

With the introduction of the [Doctrine 2 ORM](http://www.doctrine-project.org/) a start was made to integrate parts of the [Symfony 2 framework](http://symfony.com/). The ORM took over all of the database write/delete/update queries and some of the read queries. So reducing the amount of legacy code already

The first step was to introduce an AppKernel with a Janus CoreBunde for Janus dependencies, a SSPIntegrationBundle for integration with [SimpleSamlPhp](https://simplesamlphp.org/) and various vendor bundles like the [DoctrineBundle](https://github.com/doctrine/DoctrineBundle). This also introduced a few new directories:
- `app`: The Symfony application and config
- `src`: The new domain and Symfony bundle code

Since the first attempt to use Doctrine was done without using Symfony a separate Dependency injection container based on [Pimple](http://pimple.sensiolabs.org/) was added, part of this still remains

The second step was to add REST functionality which added another dir
- `web`: The Symfony app index file

The 3rd step was to combine all the existing configuration of Janus and the new Config which came with Doctrine and Symfony. The existing config was a php file located in the SimpleSamlPhp `config` dir. The Symfony config was in Yaml files located in the `app\config` dir (and the various bundles).
The chosen solution was to adapt the Symfony config style which meant that the existing Janus configuration had to be converted to Yaml. This was also an ideal oppurtunity to change the format of some configuration to a more desired format. In some cases this was even demanded by symfony. All config will now be parsed by Symfony so a [config definition](https://github.com/janus-ssp/janus/blob/develop/src/Janus/ServiceRegistry/Bundle/CoreBundle/DependencyInjection/Configuration.php) had to be created. this configuration is currently quite basic but could be improved by adding more default values, validation rules etc.

Converting existing configurations from php to yaml can be automated by using the `bin/migrateConfig` command.

## Present

Currently two codebases exist more or less next each other sometimes with confusing directory names: `www` versus `web` or `lib` versus `src`. All new code is namespaced using the [PSR-2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)


## Future

All legacy code should be gradually refactored to blend in the new style of code. This means:
- All classes in the `lib` dir should be evaluated and combined/split up where needed and at least conform to the PSR-2 coding standard. Furthermore they need to be tested, this will probably introduce some injection refactoring.


These are the parts that currently remain in the `lib` dir:

# Symfony integration

## Past

With the introduction of the [Doctrine 2 ORM](http://www.doctrine-project.org/) a start was made to integrate parts of the [Symfony 2 framework](http://symfony.com/). The ORM took over all of the database write/delete/update queries and some of the read queries. So reducing the amount of legacy code already

The first step was to introduce an AppKernel with a Janus CoreBunde for Janus dependencies, a SSPIntegrationBundle for integration with [SimpleSamlPhp](https://simplesamlphp.org/) and various vendor bundles like the [DoctrineBundle](https://github.com/doctrine/DoctrineBundle). This also introduced a few new directories:
| `app`: The Symfony application and config
| `src`: The new domain and Symfony bundle code

Since the first attempt to use Doctrine was done without using Symfony a separate Dependency injection container based on [Pimple](http://pimple.sensiolabs.org/) was added, part of this still remains

The second step was to add REST functionality which added another dir
| `web`: The Symfony app index file

The 3rd step was to combine all the existing configuration of Janus and the new Config which came with Doctrine and Symfony. The existing config was a php file located in the SimpleSamlPhp `config` dir. The Symfony config was in Yaml files located in the `app\config` dir (and the various bundles).
The chosen solution was to adapt the Symfony config style which meant that the existing Janus configuration had to be converted to Yaml. This was also an ideal oppurtunity to change the format of some configuration to a more desired format. In some cases this was even demanded by symfony. All config will now be parsed by Symfony so a [config definition](https://github.com/janus-ssp/janus/blob/develop/src/Janus/ServiceRegistry/Bundle/CoreBundle/DependencyInjection/Configuration.php) had to be created. this configuration is currently quite basic but could be improved by adding more default values, validation rules etc.

Converting existing configurations from php to yaml can be automated by using the `bin/migrateConfig` command.

## Present

Currently two codebases exist more or less next each other sometimes with confusing directory names: `www` versus `web` or `lib` versus `src`. All new code is namespaced using the [PSR-2 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)


## Future

All legacy code should be gradually refactored to blend in the new style of code. This means:
- All classes in the `lib` dir should be evaluated and combined/split up where needed and at least conform to the PSR-2 coding standard. Furthermore they need to be tested, this will probably introduce some injection refactoring.


These are the parts that currently remain in the `lib` dir:

| Dir/File | Todo/Desc. |
|----------|-----|
| `Cron` | Not used by OpenConext, if no other users use it remove it | 
| `Exception` | Just move somewhere in `src` dir |
| `Exporter` | Apart from this export there are also the new REST API, the Janus Tools, see if this is still needed. |
| `Messenger` | Not used by OpenConext, if no other users use it remove it | 
| `Metadata` | Metadata converter, already a bit more modern, is used for importing (which is currently broken) |
| `REST` | Old API can be removed when the new API is approved (almost!) |
| `Validation` | A bunch of basic validation functions, can be easily translated to proper validators |
| `Xml` | A converter for xs:duration to unix timestamp., according to @relaxnow this is no longer necessary |
| `AdminUtil.php` | A bunch of connection related queries which need to be move to the connection service and repository |
| `CertificateFactory.php` | A factory for certificates, can probably be moved to the [x509 validate lib](https://github.com/janus-ssp/php-x509-validate)|
| `CustomDictionaryLoader.php` | A workaround for adding extra translations to a SimpleSamlPhp based template, can be removed if no templates use this anymore |
| `Database.php` | Base class for classes which do not (fully) use the ORM yet, can be removed when this is resolved |
| `DiContainer.php` | Di container which is used to get dependencies of which the most already defer to the Symfony service container. Can be removed when all requests are run through symfony AND all dependencies are properly injected an [issue](https://github.com/janus-ssp/janus/issues/453) for this has been created. |
| `Entity.php` | An 'active record' like implementation for connections, should be merged with the existing service and repository and replaced with a DTO |
| `EntityController.php` | A 'active record factory'?! style implementation for connections, should also be merged with existing service and repository |
| `Exporter.php` | Part of the Exporter package, see above. |
| `Messenger.php` |Part of the Messenger package, see above. |
| `MetaExport.php` | |
| `Metadata.php` | A Write DTO like object for metadata information, it's functionality should be moved elsewhere to make it a real DTO. A `sspmod_janus_Metadata` object is constructed based on `sspmod_janus_MetadataField` object |
| `MetadataField.php` | A Read DTO like object for metadata information, it's functionality should be moved elsewhere to make it a real DTO. |
| `MetadataFieldBuilder.php` | A Builder for building Metadatafield objects |
| `Postman.php` | A notifier which notifies when a new user has been created. Not used by OpenConext, might be removed.  |
| `User.php` | An 'active record' like implementation for users, should be merged with the existing service and repository and replaced with a DTO |
| `UserController.php` | A bunch of use AND connection related queries which should be moved to their corresponding services and repositories. |


Aside from classes there are is also a frontend part which is located in the `www` and `templates` dirs. This part also needs refactoring. A general remark is that most files in the `www` dir are more or less page scripts, some are quite big, others are bigger. However the most important thing would be to move all routing to symfony. This could be achieved by wrapping the contents of the page scripts in Symfony controllers and gradually extracting business logic out of it.

All templates are php files based on the SimpleSamlPhp template. These could be split up since some templates contain code for a lot of different purposes (tabs). Each template could then be freed from it's SimpleSamlPhp dependency and converted to a more simple format like [Twig](http://twig.sensiolabs.org/) html which is also the defactor Symfony template engine.

Some templates also contain a lot of javascript, this could be extracted to separate files. 


| Dir/File            | Split up | Extract Js/Css | Convert to Twig | Desc |
|---------------------|----------|----------------|-----------------|------|
| dashboard           |  -       | -              | -               | Subtemplates that have already been splitted of the large main dashboard template |
| dashboard.php       | X        | X              | X               |      |
| editentity          | -        | -              | -               | Subtemplates that have already been splitted of the large main editentity template |
| editentity.php      | X        | X              | X               |      |
| error.php           | -        | -              | X               |      |
| metadataexport.php  | -        | X              | X               |      |
| metalisting.php     | -        | -              | X               |      |
| nonewuser.php       | -        | -              | X               |      |
| importentity.php    | -        | -              | X               |      |
| metadata.php        | -        | -              | X               |      |
| newuser.php         | -        | -              | X               |      |
| show-entities-validation.php
                      | -        | -              | X               |      |


 





 




