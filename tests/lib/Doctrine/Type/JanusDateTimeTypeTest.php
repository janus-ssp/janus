<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\MySqlPlatform;

class sspmod_janus_Doctrine_Type_JanusDateTimeTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_Doctrine_Type_JanusDateTimeType
     */
    private $dateTimeType;

    /**
     * @var Doctrine\DBAL\Platforms\MySqlPlatform
     */
    private $platform;

    public function setUp()
    {
        // Type only has to be setup once since Doctrine stores it statically
        if (!Type::hasType('janusDateTime')) {
            Type::addType('janusDateTime', 'sspmod_janus_Doctrine_Type_JanusDateTimeType');
        }
        $this->dateTimeType = Type::getType('janusDateTime');

        $this->platform = new MySqlPlatform();
    }

    public function testSqlDeclaration()
    {
        $sqlDeclaration = $this->dateTimeType->getSqlDeclaration(array(), $this->platform);
        $this->assertEquals('CHAR(25)', $sqlDeclaration);
    }

    public function testPhpValueIsConvertedToDatabaseValue()
    {
        $databaseValue = $this->dateTimeType->convertToDatabaseValue(
            new \DateTime('1980-01-26 01:01:01'),
            $this->platform
        );
        $this->assertEquals('1980-01-26T01:01:01+01:00', $databaseValue);
    }

    public function testPhpNullValueIsConvertedToDatabaseNullValue()
    {
        $databaseValue = $this->dateTimeType->convertToDatabaseValue(
            null,
            $this->platform
        );
        $this->assertEquals(null, $databaseValue);
    }

    public function testDatabaseValueIsConvertedToPhpValue()
    {
        $phpValue = $this->dateTimeType->convertToPhpValue(
            '1980-01-26 01:01:01',
            $this->platform
        );
        $this->assertEquals(new \DateTime('1980-01-26 01:01:01'), $phpValue);
    }

    public function testDatabaseNullValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->dateTimeType->convertToPhpValue(
            null,
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }

    public function testInvalidDatabaseValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->dateTimeType->convertToPhpValue(
            'foo',
            $this->platform
        );

        $this->assertEquals(null, $phpValue);
    }
}