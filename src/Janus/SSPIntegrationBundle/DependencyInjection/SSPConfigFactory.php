<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace Janus\SSPIntegrationBundle\DependencyInjection;

use SimpleSAML_Configuration;

class SSPConfigFactory
{
    public function create()
    {
        return SimpleSAML_Configuration::getConfig('module_janus.php');
    }
}