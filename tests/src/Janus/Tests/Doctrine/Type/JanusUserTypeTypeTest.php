<?php
namespace Janus\Tests\Doctrine\Type;

use PHPUnit_Framework_TestCase;

use Janus\Doctrine\Type\JanusUserTypeType;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\MySqlPlatform;

class JanusUserTypeTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JanusUserTypeType
     */
    private $userTypeType;

    /**
     * @var MySqlPlatform
     */
    private $platform;

    public function setUp()
    {
        // Type only has to be setup once since Doctrine stores it statically
        if (!Type::hasType('janusUserType')) {
            Type::addType('janusUserType', 'Janus\Doctrine\Type\JanusUserTypeType');
        }
        $this->userTypeType = Type::getType('janusUserType');

        $this->platform = new MySqlPlatform();
    }

    public function testSqlDeclaration()
    {
        $sqlDeclaration = $this->userTypeType->getSqlDeclaration(array(), $this->platform);
        $this->assertEquals('VARCHAR(255)', $sqlDeclaration);
    }

    public function testPhpValueIsConvertedToDatabaseValue()
    {
        $databaseValue = $this->userTypeType->convertToDatabaseValue(
            array('admin'),
            $this->platform
        );
        $this->assertEquals('a:1:{i:0;s:5:"admin";}', $databaseValue);
    }

    public function testPhpNullValueIsConvertedToDatabaseNullValue()
    {
        $databaseValue = $this->userTypeType->convertToDatabaseValue(
            null,
            $this->platform
        );
        $this->assertEquals('N;', $databaseValue);
    }

    public function testDatabaseValueIsConvertedToPhpValue()
    {
        $phpValue = $this->userTypeType->convertToPhpValue(
            'a:1:{i:0;s:5:"admin";}',
            $this->platform
        );
        $this->assertEquals(array('admin'), $phpValue);
    }

    public function testDatabaseNullValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->userTypeType->convertToPhpValue(
            'N;',
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }

    public function testInvalidDatabaseValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->userTypeType->convertToPhpValue(
            'foo',
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }
}