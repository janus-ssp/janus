<?php

require_once 'lib/Command.php';
require_once 'lib/FlattenMetadataKeys.php';

class FlattenMetadataKeysTest extends PHPUnit_Framework_TestCase
{
    private $f;

    public function setUp()
    {
        $this->f = new sspmod_janus_FlattenMetadataKeys();
    }

    /**
     * @dataProvider provider
     */
    public function testFlattening($data, $result)
    {
        $this->assertEquals($result, $this->f->exec($data));
    }

    public function provider()
    {
        return array(
          array(
                array (
                  'UIInfo' =>
                  array (
                    'Keywords' =>
                    array (
                      'en' => 'foo bar baz',
                    ),
                  ),
                ),
                array("UIInfo:Keywords:en" => "foo bar baz")
            ),
            array(array(), array()),
            array(array("foo" => "bar"), array("foo" => "bar")),
            array(array("foo" => array("foo" => "bar")), array("foo:foo" => "bar")),
        );
    }
}
