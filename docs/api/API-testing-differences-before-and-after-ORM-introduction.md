Janus v1.17 introduces of Doctrine ORM. To make sure the API still works the same as it did before two things have to be compared:

* Data integrity
* Performance

## Comparing data
To make sure both API's return the same data a test has been created, this test compares two installs of Janus, one version 1.16 and one version 1.17. This test retrieves all Sp connections and all Idp connections from the API, tests all known calls for each individual connection on both API's and compares both results (there should not be any differences)

The data which will be retrieved and compared from the API for an Sp these are:
* The list of Sp's itself **Note: disable consent is reordered for comparison**
* Details **Note: arp is ignored from comparison since it has changed from an id to an array of attributes, user is ignored from comparison since some users are set to null if they did not exist**
* Metadata
* Arp
* Check if connection to given Idp is allowed
* Allowed Idp connections **Note: is reordered for comparison**

For an Idp these are:
* The list of idp's itself
* Details **Note: arp is ignored from comparison since it has changed from an id to an array of attributes, user is ignored from comparison since some users are set to null if they did not exist**
* Metadata
* Check if connection to given Sp is allowed
* Allowed Sp connections **Note: is reordered for comparison**

Furthermore the test also compares:
* users
* 'search identifiers by metadata' result

## Comparing performance
Aside from comparing the output of a call to the API it's also interesting to know if the perfomance is at least as good as it was. Therefore the time required to get a response from the the API will be measured and compared per result. 

Since a lot of connection data is cached into memcache it is important to that both systems use different memcache cache. This can be achieved by changing the cache keys at one of the instances by prefixing something to them (will be explained later on).

Al tests can be executed by running a specific test:
``./bin/phpunit -vvv --debug tests-for-orm-introduction/compareApiTest.php``

## How to set up testing environment:

### Update Janus
Make sure you Janus installation is on the 'compare-api-performance' branch and up to date.

### Install old version of Janus
Checkout the last version of janus before the ORM introduction: 1.16 (I used OpenConext ServiceRegistry: https://github.com/OpenConext/OpenConext-serviceregistry/tree/3.9.1) alongside the current install.

Make both instances of Janus use different keys for storing cached entities. This can be achieved by suffixing the memcache keys with string of choice e.g: ``$key .= '-old'``. This has to be done at:
* https://github.com/simplesamlphp/simplesamlphp/blob/master/lib/SimpleSAML/Memcache.php#L35
* https://github.com/simplesamlphp/simplesamlphp/blob/master/lib/SimpleSAML/Memcache.php#L122 
* https://github.com/simplesamlphp/simplesamlphp/blob/master/lib/SimpleSAML/Memcache.php#L146

Create an extra vhost for the old janus install like:

(Note: if you use a different path or hostname do not forget to change this in the test class.

### Use prod database
To make sure the tests comes as close as possible to a production environment, the production database needs to be used. This can be done by:
- Importing a dump of 2 databases
- Let the old version use one of the new databases
- Update the other to the new db structure by running Doctrine Migrations and let the new version use that one. I did this by placing the prod dump in ``~/janus-db-export-prod/`` and running ``./tests-for-orm-introduction/compareDoctrineMigrationsTest.sh`` from the janus root


```
<VirtualHost *:443>
    ServerAdmin root@localhost
    DocumentRoot /opt/www/serviceregistry-janus-1.16/www
    ServerName serviceregistry-janus-1.16.demo.openconext.org
    ErrorLog                logs/sr_error_log
    TransferLog             logs/sr_access_log

    Alias /simplesaml /opt/www/serviceregistry-janus-1.16/www

    SSLEngine on

    SSLProtocol -ALL +SSLv3 +TLSv1
    SSLCipherSuite ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:!RC4-MD5:RC4+RSA:+HIGH:+MEDIUM

    SSLCertificateFile    /etc/httpd/keys/openconext.pem
    SSLCertificateKeyFile /etc/httpd/keys/openconext.key
    SSLCACertificateFile  /etc/httpd/keys/openconext_ca.pem
</VirtualHost>
```
Where to find the code?
* Test: https://github.com/janus-ssp/janus/blob/master/tests-for-orm-introduction/compareApiTest.php