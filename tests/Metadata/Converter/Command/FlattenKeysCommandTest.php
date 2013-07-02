<?php

class Metadata_Converter_Command_FlattenKeysCommandTest extends PHPUnit_Framework_TestCase
{
    private $flattenKeysCommand;

    public function setUp()
    {
        $this->flattenKeysCommand = new sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand();
    }

    /**
     * @dataProvider provider
     */
    public function testFlattening($data, $result)
    {
        $this->assertEquals($result, $this->flattenKeysCommand->convert($data));
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
