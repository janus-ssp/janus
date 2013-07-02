<?php

require_once 'lib/Command.php';
require_once 'lib/FlattenMetadataValues.php';

class FlattenMetadataValuesTest extends PHPUnit_Framework_TestCase
{
    private $f;

    public function setUp()
    {
        $this->f = new sspmod_janus_FlattenMetadataValues();
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
          array(array (
                  'UIInfo' =>
                  array (
                    'Keywords' =>
                    array (
                      'en' =>
                      array (
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
                      ),
                    ),
                  ),
                ),
                array (
                  'UIInfo' =>
                  array (
                    'Keywords' =>
                    array (
                      'en' => 'foo bar baz',
                    ),
                  ),
                )
            ),
            array(array(), array()),
            array(array("foo" => array("foo" => "bar")), array("foo" => array("foo" => "bar"))),
        );
    }
}
