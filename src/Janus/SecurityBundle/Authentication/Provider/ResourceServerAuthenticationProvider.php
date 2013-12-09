<?php

namespace Janus\SecurityBundle\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Janus\SecurityBundle\Authentication\Token\ResourceServerToken;

class ResourceServerAuthenticationProvider implements AuthenticationProviderInterface
{

    private $oauthUrl;
    private $oauthKey;
    private $oauthSecret;
    private $oauthAccessToken;

    public function __construct($oauthUrl,
                                $oauthKey,
                                $oauthSecret,
                                $oauthAccessToken)
    {
        $this->oauthUrl = $oauthUrl;
        $this->oauthAccessToken = $oauthAccessToken;
        $this->oauthKey = $oauthKey;
        $this->oauthSecret = $oauthSecret;
    }

    public function authenticate(TokenInterface $token)
    {
        if ($this->supports($token)) {
            $accessToken = $token->getCredentials();

            $user = $this->getUserArray($accessToken);

            if ($user) {
                $authenticatedToken = new ResourceServerToken($accessToken);
                $authenticatedToken->setUser($user);
                return $authenticatedToken;
            }
        }
        throw new AuthenticationException('The OAuth2 Resource Server authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof ResourceServerToken;
    }

    private function getUserArray($accessToken)
    {
        // curl -k -v -H "Authorization: Basic Y3NhOmNzYS1zZWNyZXQ=" "https://apis.demo.openconext.org/v1/tokeninfo?access_token=ca2b078e-3316-4bf9-8f46-26ed2fb8ca18"
        //https://github.com/SURFconext/versexport/blob/master/lib/Rest/ClientCredentialsClient.php

//        $client = new Zend_Http_Client($url);
//        $client->setConfig(array('timeout' => 15));
//        $response = $client->setConfig(array('timeout' => 15))
//            ->setHeaders(Zend_Http_Client::CONTENT_TYPE, Zend_Http_Client::ENC_URLENCODED)
//            ->setAuth($conf->key, $conf->secret)
//            ->setParameterPost('grant_type', 'client_credentials')
//            ->request('GET');
//        $result = json_decode($response->getBody(), true);

        // https://github.com/OpenConextApps/apis#authorization-server---resource-server-demo-flow
        // curl -k -v -H "Authorization: Basic Y3NhOmNzYS1zZWNyZXQ=" "https://apis.demo.openconext.org/v1/tokeninfo?access_token=ca2b078e-3316-4bf9-8f46-26ed2fb8ca18"

        $json = <<<EOT
{
    "audience": "VM Integration Tests",
    "scopes": [
        "read",
        "write"
    ],
    "principal": {
        "name": "VM Integration Tests",
        "roles": [],
        "groups": [],
        "adminPrincipal": false,
        "attributes": {}
    },
    "expires_in": 1373178401833
}
EOT;
        return json_decode($json, true);
    }

    private function _ensureTrailingSlash($configuredUrl)
    {
        $slash = '/';
        $configuredUrl = (substr($configuredUrl, -strlen($slash)) === $slash) ? $configuredUrl : $configuredUrl . $slash;
        return $configuredUrl;
    }
}