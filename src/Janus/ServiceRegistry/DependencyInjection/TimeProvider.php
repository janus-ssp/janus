<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

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