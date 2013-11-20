This dir is meant to store doctrine proxies, these do not have to be commited but have to be generated somehow:
* either by configuring:
'doctrine.proxy_auto_generate' => true,

or by having them generated using Doctrine CLI tool:
./bin/doctrine orm:generate-proxies