<?php

require_once 'lib/Command.php';
require_once 'lib/MapMetadataKeys.php';

class MapMetadataKeysTest extends PHPUnit_Framework_TestCase
{
    private $f;

    public function setUp()
    {
        $this->f = new sspmod_janus_MapMetadataKeys();
    }

    /**
     * @dataProvider provider
     */
    public function testMapping($data, $result, $mapping)
    {
        $this->f->setMapping($mapping);
        $this->assertEquals($result, $this->f->exec($data));
    }

    public function provider()
    {
        return array(
            array(
                array("foo" => "bar", "foobar" => "baz"),
                array("bar" => "bar", "foobar" => "baz"),
                array("foo" => "bar"),
            ),
        );
    }
}
