<?php

class sspmod_janus_Metadata_Converter_Command_FlattenValuesCommandTest extends PHPUnit_Framework_TestCase
{
    /** @var sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand */
    private $flattenValuesCommand;

    public function setUp()
    {
        $this->flattenValuesCommand = new sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand();
    }

    /**
     * @dataProvider provider
     */
    public function testFlattening($data, $result)
    {
        $this->assertEquals($result, $this->flattenValuesCommand->convert($data));
    }

    public function provider()
    {
        return array(
            array(
                array(
                    'UIInfo' => array (
                        'Keywords' => array (
                            'en' => array ("foo", "bar", "baz"),
                        ),
                    ),
                ),
                array(
                    'UIInfo' => array (
                        'Keywords' => array (
                            'en' => 'foo bar baz',
                        ),
                    ),
                )
            ),
            array(
                array(),
                array()
            ),
            array(
                array(
                    "foo" => array(
                        "foo" => "bar"
                    ),
                ),
                array(
                    "foo" => array(
                        "foo" => "bar"
                    )
                ),
            ),
        );
    }
}
