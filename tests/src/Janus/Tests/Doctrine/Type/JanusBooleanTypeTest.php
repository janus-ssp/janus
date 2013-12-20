<?php
namespace Janus\Tests\Doctrine\Type;

use PHPUnit_Framework_TestCase;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\MySqlPlatform;

class JanusBooleanTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Janus\Doctrine\Type\JanusBooleanType
     */
    private $booleanType;

    /**
     * @var Doctrine\DBAL\Platforms\MySqlPlatform
     */
    private $platform;

    public function setUp()
    {
        // Type only has to be setup once since Doctrine stores it statically
        if (!Type::hasType('janusBoolean')) {
            Type::addType('janusBoolean', 'Janus\Doctrine\Type\JanusBooleanType');
        }
        $this->booleanType = Type::getType('janusBoolean');

        $this->platform = new MySqlPlatform();
    }

    public function testSqlDeclaration()
    {
        $sqlDeclaration = $this->booleanType->getSqlDeclaration(array(), $this->platform);
        $this->assertEquals('CHAR(3)', $sqlDeclaration);
    }

    public function testPhpTrueValueIsConvertedToDatabaseTrueValue()
    {
        $databaseValue = $this->booleanType->convertToDatabaseValue(
            true,
            $this->platform
        );
        $this->assertEquals('yes', $databaseValue);
    }

    public function testPhpFalseValueIsConvertedToDatabaseFalseValue()
    {
        $databaseValue = $this->booleanType->convertToDatabaseValue(
            false,
            $this->platform
        );
        $this->assertEquals('no', $databaseValue);
    }

    public function testDatabaseTrueValueIsConvertedToPhpTrueValue()
    {
        $phpValue = $this->booleanType->convertToPhpValue(
            'yes',
            $this->platform
        );
        $this->assertEquals(true, $phpValue);
    }

    public function testDatabaseFalseValueIsConvertedToPhpFalseValue()
    {
        $phpValue = $this->booleanType->convertToPhpValue(
            'no',
            $this->platform
        );
        $this->assertEquals(false, $phpValue);
    }

    public function testInvalidDatabaseValueIsConvertedToPhpFalseValue()
    {
        $phpValue = $this->booleanType->convertToPhpValue(
            'foo',
            $this->platform
        );
        $this->assertEquals(false, $phpValue);
    }
}