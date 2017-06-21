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
     *              'soure' => 'sourceName' (if specifically set)
     *          ],     *          [
     *              'value' => 'filterValue'
     *          ],
     *      ]
     *  ]
     * @param $attributes
     * @param $sources
     * @return array
     */
    public function mergeAttributes($attributes, $sources)
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
}
