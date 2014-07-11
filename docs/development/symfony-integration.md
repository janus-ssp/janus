# Symfony integration

With the introduction of the Doctrine 2 ORM a start was made to integrate parts of the Symfony 2 framework. 

The first step was to introduce an AppKernel with a Janus CoreBunde for Janus dependencies, a SSPIntegrationBundle for integration with SimpleSamlPhp and various vendor bundles like the DoctrineBundle. This also introduced a few new directories:
- `app`: The Symfony application and config
- `src`: The new domain and Symfony bundle code

Since the first attempt to use Doctrine was done without using Symfony a separate Dependency injection container based on Pimple was added, part of this still remains

The second step was to add REST functionality which added another dir
- `web`: The Symfony main file

