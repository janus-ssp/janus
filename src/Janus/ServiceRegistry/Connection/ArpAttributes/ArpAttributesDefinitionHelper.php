<?php

namespace Janus\ServiceRegistry\Connection\ArpAttributes;

use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;

/**
 * Class ArpAttributesHelper
 */
class ArpAttributesDefinitionHelper
{
    /**
     * @var ConfigProxy
     */
    protected $janusConfig;

    /**
     * @param ConfigProxy $janusConfig
     */
    public function __construct(ConfigProxy $janusConfig)
    {
        $this->janusConfig = $janusConfig;
    }

    /**
     * Appends the source of the ArpAttribute from the ConfigProxy to the list of configured ARP attributes.
     * @param $inputArpAttributes
     * @return array|string
     */
    public function appendSource($inputArpAttributes)
    {
        // If the input is string, do not add sources. And only try to append sources if the attributes exist in config.
        if ($this->janusConfig->hasValue('attributes') && !is_string($inputArpAttributes)) {
            foreach ($this->janusConfig->getArray('attributes') as $arpAttribute){

                $attributeHasSource = array_key_exists('source', $arpAttribute);
                if ($attributeHasSource) {
                    $name = $arpAttribute['name'];
                    $source = $arpAttribute['source'];

                    $nameInArpAttributes = array_key_exists($name, $inputArpAttributes);
                    if ($nameInArpAttributes) {
                        $inputArpAttributes[$name][] = $source;
                    }
                }
            }
        }
        // By default return the inputted value, it might have been appended with sources.
        return $inputArpAttributes;
    }
}
