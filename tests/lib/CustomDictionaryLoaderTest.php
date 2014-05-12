<?php

class CustomDictionaryLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var sspmod_janus_CustomDictionaryLoader
     */
    private $dictionaryLoader;

    /**
     * @var SimpleSAML_XHTML_Template|Phake_IMock
     */
    private $templateMock;

    /**
     * @var string
     */
    private $resourcesDir;

    public function setUp()
    {
        Phake::setClient(Phake::CLIENT_PHPUNIT);
        $this->templateMock = Phake::mock('SimpleSAML_XHTML_Template');
        $this->resourcesDir = realpath(__DIR__ . '/CustomDictionaryLoaderTestResources');
    }

    public function testAddsTranslationToTemplate()
    {
        $this->createLoader('testlangcode');

        $dictionariesDir = $this->resourcesDir . '/dictionaries';
        $this->dictionaryLoader->addFromDir($dictionariesDir);
        Phake::verify($this->templateMock)
            ->includeInlineTranslation('{janus:testDictionary:testTranslation}', 'test value');
//        $this->assert
    }

    public function testSkipsMissingTranslationsForLanguage()
    {
        $this->createLoader('otherlangcode');

        $dictionariesDir = $this->resourcesDir . '/dictionaries';
        $this->dictionaryLoader->addFromDir($dictionariesDir);
        Phake::verify($this->templateMock, Phake::times(0))
            ->includeInlineTranslation(Phake::anyParameters());
    }

    public function testSkipsDictionariesWithoutCustomSuffixInFilenameCustom()
    {
        $this->createLoader('testlangcode');


        $dictionariesDir = $this->resourcesDir . '/no-dictionaries';
        $this->dictionaryLoader->addFromDir($dictionariesDir);
        Phake::verify($this->templateMock, Phake::times(0))
            ->includeInlineTranslation(Phake::anyParameters());
    }

    private function createLoader($langCode)
    {
        Phake::when($this->templateMock)
            ->getLanguage(Phake::anyParameters())
            ->thenReturn($langCode);

        $this->dictionaryLoader = new sspmod_janus_CustomDictionaryLoader($this->templateMock);
    }
}