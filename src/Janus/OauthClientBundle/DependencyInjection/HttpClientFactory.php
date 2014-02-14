<?php
namespace Janus\OauthClientBundle\DependencyInjection;

use Guzzle\Http\Client;

/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
class HttpClientFactory
{
    /**
     * @param string $url
     * @return Client
     */
    public function create($url)
    {
        return new Client($url);
    }
}