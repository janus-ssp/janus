<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */
namespace Janus\Tests\ServiceRegistry\DependencyInjection;

use DateTime;

use Janus\ServiceRegistry\DependencyInjection\TimeProvider;

/**
 * Mocked time provider which always returns the same time for tests
 *
 * @todo create a better and more flexible solution for this
 */
class TimeProviderMock extends TimeProvider
{
    public function getDateTime() {
        return new DateTime('1970-01-01 00:00:00');
    }
}