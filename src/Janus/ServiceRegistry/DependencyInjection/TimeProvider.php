<?php
namespace Janus\ServiceRegistry\DependencyInjection;

use DateTime;

/**
 * @author Lucas van lierop
 */
class TimeProvider
{
    /**
     * Provides current time
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return new DateTime();
    }
}