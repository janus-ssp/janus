<?php

namespace Janus\SecurityBundle\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Janus\SecurityBundle\Authentication\Token\ResourceServerToken;
use Guzzle\Http\Client;

class ResourceServerAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var Client
     */
    private $httpClient;

    private $oauthKey;
    private $oauthSecret;
    private $oauthAccessToken;
    private $oauthAllowSelfSignedCert;

    public function __construct(
        Client $httpClient,
        $oauthKey,
        $oauthSecret,
        $oauthAccessToken,
        $oauthAllowSelfSignedCert
    )
    {
        $this->httpClient = $httpClient;
        $this->oauthAccessToken = $oauthAccessToken;
        $this->oauthKey = $oauthKey;
        $this->oauthSecret = $oauthSecret;
        $this->oauthAllowSelfSignedCert = $oauthAllowSelfSignedCert;
    }

    public function authenticate(TokenInterface $token)
    {
        if ($this->supports($token)) {
            $accessToken = $token->getCredentials();

            $user = (empty($this->oauthAccessToken) ? $this->getUserArray($accessToken) :
                $this->getPredefinedUserArray($accessToken));

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
        $request = $this->httpClient->get('v1/tokeninfo')->setAuth($this->oauthKey, $this->oauthSecret);
        $request->getQuery()->add('access_token', $accessToken);
        $this->sslOptions($request);
        $json = $request->send()->getBody();
        return json_decode($json, true);
    }

    private function getPredefinedUserArray($accessToken)
    {
        if ($this->oauthAccessToken != $accessToken) {
            return null;
        }
        return array(
            'audience' => 'test-client',
            'scopes' => array('actions'),
            'principal' => array('name' => 'test-client', 'attributes' => array()));
    }

    private function _ensureTrailingSlash($configuredUrl)
    {
        // @todo what about just using trim /?
        $slash = '/';
        $configuredUrl = (substr($configuredUrl, -strlen($slash)) === $slash) ? $configuredUrl : $configuredUrl . $slash;
        return $configuredUrl;
    }

    private function sslOptions($request)
    {
        if ($this->oauthAllowSelfSignedCert) {
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
        }
        $request->getCurlOptions()->set(CURLOPT_FRESH_CONNECT, true);
        $request->getCurlOptions()->set(CURLOPT_FORBID_REUSE, true);
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 1);
    }
}