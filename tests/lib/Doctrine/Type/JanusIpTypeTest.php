<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\MySqlPlatform;

class sspmod_janus_Doctrine_Type_JanusIpTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Doctrine_Type_JanusIpType
     */
    private $ipType;

    /**
     * @var Doctrine\DBAL\Platforms\MySqlPlatform
     */
    private $platform;

    public function setUp()
    {
        // Type only has to be setup once since Doctrine stores it statically
        if (!Type::hasType('janusIp')) {
            Type::addType('janusIp', 'sspmod_janus_Doctrine_Type_JanusIpType');
        }
        $this->ipType = Type::getType('janusIp');

        $this->platform = new MySqlPlatform();
    }

    public function testSqlDeclaration()
    {
        $sqlDeclaration = $this->ipType->getSqlDeclaration(array(), $this->platform);
        $this->assertEquals('CHAR(39)', $sqlDeclaration);
    }

    public function testPhpValueIsConvertedToDatabaseValue()
    {
        $databaseValue = $this->ipType->convertToDatabaseValue(
            new sspmod_janus_Model_Ip('127.0.0.1'),
            $this->platform
        );
        $this->assertEquals('127.0.0.1', $databaseValue);
    }

    public function testPhpNullValueIsConvertedToDatabaseNullValue()
    {
        $databaseValue = $this->ipType->convertToDatabaseValue(
            null,
            $this->platform
        );
        $this->assertEquals(null, $databaseValue);
    }

    public function testDatabaseValueIsConvertedToPhpValue()
    {
        $phpValue = $this->ipType->convertToPhpValue(
            '127.0.0.1',
            $this->platform
        );
        $this->assertEquals(new sspmod_janus_Model_Ip('127.0.0.1'), $phpValue);
    }

    public function testDatabaseNullValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->ipType->convertToPhpValue(
            null,
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }

    public function testInvalidDatabaseNullValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->ipType->convertToPhpValue(
            '123',
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }
}