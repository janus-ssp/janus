<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection;

use PHPUnit_Framework_TestCase;
use Phake;

use Janus\ServiceRegistry\Entity\Connection;

class RevisionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
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
        $this->connection = Phake::mock('Janus\ServiceRegistry\Entity\Connection');
        $this->revisionNr = 0;
        $this->parentRevisionNr = 1;
        $this->revisionNote = 'test';
        $this->state = 'test';
        $this->expirationDate = new \DateTime();
        $this->metadataUrl = '';
        $this->allowAllEntities = true;
        $this->arpAttributes = null;
        $this->manipulation = '';
        $this->isActive = true;
        $this->notes = 'some notes';
    }

    public function testInstantiation()
    {
        $connectionRevision = new Connection\Revision(
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

        $this->assertInstanceOf('Janus\ServiceRegistry\Entity\Connection\Revision', $connectionRevision);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Invalid revision note ''
     */
    public function testInstantiationFailsWithEmptyRevisionNote()
    {
        new Connection\Revision(
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