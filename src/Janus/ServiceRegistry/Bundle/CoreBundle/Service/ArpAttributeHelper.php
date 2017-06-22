<?php

namespace Janus\ServiceRegistry\Bundle\CoreBundle\Service;

/**
 * This helper class can be used to perform Arp Attribute related transformation.
 *
 * @package Janus\ServiceRegistry\Bundle\CoreBundle\Service
 */
final class ArpAttributeHelper
{
    const ARP_DEFAULT_SOURCE = 'idp';

    /**
     * Merge the arp attributes and the sources.
     *
     * The array is stored like this:
     *  [
     *      attrName => [
     *          [
     *              'value' => 'filterValue'
     *              'source' => 'sourceName' (if specifically set)
     *          ],
     *          [
     *              'value' => 'filterValue'
     *          ],
     *      ]
     *  ]
     * @param array $attributes
     * @param array $sources
     * @return array
     */
    public function mergeAttributes(array $attributes, array $sources)
    {
        $output = array();
        foreach ($attributes as $attribute => $values) {
            foreach ($values as $value) {
                $attrSourceCombination = array('value' => $value);
                if (isset($sources[$attribute]) && $sources[$attribute] !== self::ARP_DEFAULT_SOURCE) {
                    $attrSourceCombination['source'] = $sources[$attribute];
                }
                $output[$attribute][] = $attrSourceCombination;
            }
        }
        return $output;
    }

    /**
     * Adds the default arp attribute source (idp) to the beginning of the input array.
     *
     * @param $sources
     * @return mixed
     */
    public function addDefaultAttributeSource($sources)
    {
        array_unshift($sources, self::ARP_DEFAULT_SOURCE);
        return $sources;
    }

    /**
     * Returns the selected source for the attribute. By default the ARP_DEFAULT_SOURCE is returned.
     *
     * @param $attributeInformation
     * @return string
     */
    public function getSelectedSource($attributeInformation)
    {
        // see if the attribute information contains source information
        $sources = array_unique(array_column($attributeInformation, 'source'));
        // there shoud be one source
        if (!empty($sources) && sizeof($sources) == 1) {
            return array_pop($sources);
        }
        // By default the default source is selected
        return self::ARP_DEFAULT_SOURCE;
    }

    /**
     * To guarantee backwards compatibility retrieve the filter value for a given ARP attribute based on the type.
     * The new situation will have an array as value, in the old situation a string was used.
     *
     * @param $value
     * @return mixed
     */
    public function getAttributeFilterValue($value)
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value) && array_key_exists('value', $value)) {
            return $value['value'];
        }
        return '';
    }
}
