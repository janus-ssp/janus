<?php
namespace Janus\Tests\ServiceRegistry\Entity\Connection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Janus\ServiceRegistry\Bundle\CoreBundle\DependencyInjection\ConfigProxy;
use PHPUnit_Framework_TestCase;
use Phake;
use ReflectionClass;

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
        $this->arpAttributes = '';
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

    public function testConvertsMetadatatoDto()
    {
        // @todo cleanup
        Phake::when($this->connection)->getType()->thenReturn('saml20-idp');

        // Created Connection to save
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
            $this->notes,
            array(),
            array(),
            array()
        );

        // Created Persistent metadata collection
        $collection =new ArrayCollection(array(
            new Connection\Revision\Metadata($connectionRevision, 'foo:bar:baz', 1)
        ));

        $metadataPersistentCollection = new PersistentCollection(
            Phake::mock('Doctrine\ORM\EntityManager'),
            Phake::mock('Doctrine\ORM\Mapping\ClassMetadata'),
            $collection
        );

        // Set metadata value in entity which is normally done by Doctrine ORM
        $revisionReflection = new ReflectionClass("Janus\ServiceRegistry\Entity\Connection\Revision");
        $metadataReflectionProperty = $revisionReflection->getProperty("metadata");
        $metadataReflectionProperty->setAccessible(true);
        $metadataReflectionProperty->setValue($connectionRevision, $metadataPersistentCollection);

        // Verify metadata is stored nested in dto
        $config = new ConfigProxy(array(
            "metadatafields" => array(
                'saml20_idp' => array(

                )
            )
        ));
        $connectionDto = $connectionRevision->toDto($config);
        $metadataDto = $connectionDto->getMetadata();
        $this->assertEquals(1, $metadataDto['foo']['bar']['baz']);
    }
}