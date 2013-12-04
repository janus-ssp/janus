<?php
class sspmod_janus_Model_Connection_RevisionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $revisionNr;

    /**
     * @var int
     */
    private $parentRevisionNr;

    /**
     * @var string
     */
    private $revisionNote;

    /**
     * @var string
     */
    private $state;

    /**
     * @var \DateTime
     */
    private $expirationDate;

    /**
     * @var string
     */
    private $metadataUrl;

    /**
     * @var bool
     */
    private $allowAllEntities;

    /**
     * @var string
     */
    private $arpAttributes;

    /**
     * @var string
     */
    private $manipulation;

    /**
     * @var bool
     */
    private $isActive;

    public function setUp()
    {
        $this->connection = Phake::mock('sspmod_janus_Model_Connection');
        $this->revisionNr = 0;
        $this->parentRevisionNr = 1;
        $this->revisionNote = 'test';
        $this->state = 'test';
        $this->expirationDate = new \DateTime();
        $this->metadataUrl = '';
        $this->allowAllEntities = true;
        $this->arpAttributes = '';
        $this->manipulation = '';
        $this->isActive = true;
        $this->notes = 'some notes';
    }

    public function testInstantiation()
    {
        $connectionRevision = new sspmod_janus_Model_Connection_Revision(
            $this->connection,
            $this->revisionNr,
            $this->parentRevisionNr,
            $this->revisionNote,
            $this->state,
            $this->expirationDate,
            $this->metadataUrl,
            $this->allowAllEntities,
            $this->arpAttributes,
            $this->manipulation,
            $this->isActive,
            $this->notes
        );

        $this->assertInstanceOf('sspmod_janus_Model_Connection_Revision', $connectionRevision);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Invalid revision note ''
     */
    public function testInstantiationFailsWithEmptyRevisionNote()
    {
        new sspmod_janus_Model_Connection_Revision(
            $this->connection,
            $this->revisionNr,
            $this->parentRevisionNr,
            null,
            $this->state,
            $this->expirationDate,
            $this->metadataUrl,
            $this->allowAllEntities,
            $this->arpAttributes,
            $this->manipulation,
            $this->isActive,
            null
        );
    }
}