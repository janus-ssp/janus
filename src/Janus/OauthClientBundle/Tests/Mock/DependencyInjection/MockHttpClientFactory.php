<?php
namespace Janus\OauthClientBundle\Tests\Mock\DependencyInjection;

use Janus\OauthClientBundle\DependencyInjection\HttpClientFactory;
use Phake;

/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
class MockHttpClientFactory extends HttpClientFactory
{
    /**
     * @param string $url
     * @return Client
     */
    public function create($url)
    {
        return Phake::mock('Guzzle\Http\Client');
    }
}