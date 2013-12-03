#!/bin/sh

# wrapper for remote debugging
export PHP_IDE_CONFIG="serverName=serviceregistry.demo.openconext.org"
export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=192.168.56.1"

php bin/doctrine-test/testDoctrine.php