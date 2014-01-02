<?php
namespace Janus\Tests\Doctrine\Type;

use PHPUnit_Framework_TestCase;

use Janus\Value\Ip;
use Janus\Doctrine\Type\JanusIpType;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\MySqlPlatform;

class JanusIpTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var JanusIpType
     */
    private $ipType;

    /**
     * @var MySqlPlatform
     */
    private $platform;

    public function setUp()
    {
        // Type only has to be setup once since Doctrine stores it statically
        if (!Type::hasType('janusIp')) {
            Type::addType('janusIp', 'Janus\Doctrine\Type\JanusIpType');
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
            new Ip('127.0.0.1'),
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
        $this->assertEquals(new Ip('127.0.0.1'), $phpValue);
    }

    public function testDatabaseNullValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->ipType->convertToPhpValue(
            null,
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }

    public function testInvalidDatabaseValueIsConvertedToPhpNullValue()
    {
        $phpValue = $this->ipType->convertToPhpValue(
            '123',
            $this->platform
        );
        $this->assertEquals(null, $phpValue);
    }
}