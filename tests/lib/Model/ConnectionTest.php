<?php
class sspmod_janus_Model_ConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     * @expectedExceptionMessage Name cannot be empty
     */
    public function testInstantiationWhenNameIsEmpty()
    {
        new sspmod_janus_Model_Connection(
            '',
            'saml20-idp'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Unknown connection type 'unknownType'
     */
    public function testInstantiationFailsWithUnknownType()
    {
        new sspmod_janus_Model_Connection(
            'test',
            'unknownType'
        );
    }

    public function testUpdatingCreatesRevision()
    {
        $connection = new sspmod_janus_Model_Connection(
            'a',
            'saml20-idp'
        );

        $connection->update(
            'a',
            'saml20-idp',
            null,
            'testNote',
            'test',
            new \DateTime(),
            'testurl',
            true,
            null,
            null,
            true
        );

        $this->assertInstanceOf('sspmod_janus_Model_Connection_Revision', $connection->getLatestRevision());
    }
}