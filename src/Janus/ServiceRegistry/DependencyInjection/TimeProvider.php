<?php

namespace Janus\ServiceRegistry\DependencyInjection;

use DateTime;

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