<?php
class sspmod_janus_Model_EntityRevisionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Model_Entity
     */
    private $entity;

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
    private $type;

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
     * @var sspmod_janus_Model_Entity_Revision_Arp
     */
    private $arp;

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
        $this->entity = Phake::mock('sspmod_janus_Model_Entity');
        $this->revisionNr = 0;
        $this->parentRevisionNr = 1;
        $this->revisionNote = 'test';
        $this->type = 'saml20-idp';
        $this->state = 'test';
        $this->expirationDate = new \DateTime();
        $this->metadataUrl = '';
        $this->allowAllEntities = true;
        $this->arp = Phake::mock('sspmod_janus_Model_Entity_Revision_Arp');
        $this->manipulation = '';
        $this->isActive = true;
    }

    public function testInstantiation()
    {
        $entityRevision = new sspmod_janus_Model_Entity_Revision(
            $this->entity,
            $this->revisionNr,
            $this->parentRevisionNr,
            $this->revisionNote,
            $this->type,
            $this->state,
            $this->expirationDate,
            $this->metadataUrl,
            $this->allowAllEntities,
            $this->arp,
            $this->manipulation,
            $this->isActive
        );

        $this->assertInstanceOf('sspmod_janus_Model_Entity_Revision', $entityRevision);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Unknown entity type 'unknownType'
     */
    public function testInstantiationFailsWithUnknownType()
    {
        new sspmod_janus_Model_Entity_Revision(
            $this->entity,
            $this->revisionNr,
            $this->parentRevisionNr,
            $this->revisionNote,
            'unknownType',
            $this->state,
            $this->expirationDate,
            $this->metadataUrl,
            $this->allowAllEntities,
            $this->arp,
            $this->manipulation,
            $this->isActive
        );
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Invalid revision note ''
     */
    public function testInstantiationFailsWithEmptyRevisionNote()
    {
        new sspmod_janus_Model_Entity_Revision(
            $this->entity,
            $this->revisionNr,
            $this->parentRevisionNr,
            null,
            $this->type,
            $this->state,
            $this->expirationDate,
            $this->metadataUrl,
            $this->allowAllEntities,
            $this->arp,
            $this->manipulation,
            $this->isActive
        );
    }
}