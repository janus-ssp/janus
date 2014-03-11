<?php
/**
 * Note: this test script requires the following:
 * - A new version janus available at: https://serviceregistry.demo.openconext.org
 * - An old version of janus available at:https://serviceregistry-janus-1.16.demo.openconext.org
 * - Both with a prod version of the db
 * - both with signature checking disabled
 *
 * Call with: export PHP_IDE_CONFIG="serverName=serviceregistry.demo.openconext.org" || export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=192.168.56.1" &&  clear && php tests/compareApi.php
 */

require __DIR__ . "/../app/autoload.php";

$defaultArguments = array(
    'rest' => 1,
    'user_id' => 'engine',
    'janus_key' => 'engine'
);

$methods = array(
    'getSpList' => array(),
    'getIdpList' => array(),
    'getAllowedIdps' => array(
        'spentityid' => 'https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp'
    ),
    'getEntity' => array(
        'entityid' => 'http://mock-idp'
    ),
    'isConnectionAllowed' => array(
        'spentityid' => 'https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp',
        'idpentityid' => 'http://mock-idp'

    ),
    'arp' => array(
        'entityid' => 'https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp'
    ),
    'getEntity' => array(
        'entityid' => 'https://profile.demo.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp'
    ),
    'getUser' => array(
        'userid' => 'admin'
    ),
    'getMetadata' => array(
        'entityid' => 'https://engine.demo.openconext.org/authentication/sp/metadata'
    ),
    'getAllowedSps' => array(
        'idpentityid' => 'http://mock-idp'
    ),
    'findIdentifiersByMetadata' => array(
        'key' => 'name:en',
        'value' => 'e',
        'userid' => 'admin'
    )
);

$oldHttpClient = new \Guzzle\Http\Client(
    'https://serviceregistry.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
);
$newHttpClient = new \Guzzle\Http\Client(
    'https://serviceregistry-janus-1.16.demo.openconext.org/simplesaml/module.php/janus/services/rest/'
);

foreach ($methods as $method => $methodArguments) {
    $arguments['method'] = $method;
    $arguments = array_merge($arguments, $defaultArguments, $methodArguments);

    echo "\n\ncalling old {$method}\n";

    try {
        $oldResponse = createResponse($oldHttpClient, $arguments);
    } catch (Exception $ex) {
        echo "Failed " . PHP_EOL .
            $ex->getMessage() . PHP_EOL;
        break;
    }

    echo "\n\ncalling new {$method}\n";

    try {
        $newResponse = createResponse($newHttpClient, $arguments);
    } catch (Exception $ex) {
        echo "Failed " . PHP_EOL .
            $ex->getMessage() . PHP_EOL;
        break;
    }

    // @todo diff response
    var_dump((string) $oldResponse->getBody());
    var_dump((string) $newResponse->getBody());
}

function createResponse(\Guzzle\Http\Client $client, array $arguments)
{
    $request = $client->get('', array(), array(
        'query' => $arguments
    ));
    $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
    $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

    return $request->send();
}

echo 'end';