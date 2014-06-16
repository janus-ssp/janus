<?php

/**
 * Loads custom dictionaries.
 *
 * Usage, create a file named like the file you want to override but suffixed with -custom, example:
 *
 * for: 'metadatafields.definition.json'
 * add: 'metadatafields-custom.definition.json'
 *
 * Class CustomDictionaryLoader
 * @package Janus\ServiceRegistry\Bundle\SSPIntegrationBundle\Compat
 */
class sspmod_janus_CustomDictionaryLoader
{
    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var SimpleSAML_XHTML_Template
     */
    private $template;

    /**
     * @param SimpleSAML_XHTML_Template $template
     */
    public function __construct($template)
    {
        $this->template = $template;
        $this->languageCode = $template->getLanguage();
    }

    /**
     * @param $dir
     */
    public function addFromDir($dir)
    {
        $iterator = new DirectoryIterator($dir);
        /** @var DirectoryIterator $file */
        foreach ($iterator as $file) {
            $this->addFromFile($file);
        }
    }

    /**
     * @param DirectoryIterator $file
     */
    private function addFromFile(DirectoryIterator $file)
    {
        $match = preg_match('/([^.]+)-custom.definition.json/', $file->getFilename(), $matches);
        if (!$match) {
            return;
        }
        $dictionaryName = $matches[1];

        $translations = $this->loadTranslations($file->getPathname());
        foreach ($translations as $translationName => $translations) {
            $this->includeTranslation($dictionaryName, $translationName, $translations);
        }
    }

    /**
     * @param string $filePath
     * @return mixed/opt/www/OpenConext-serviceregistry/simplesamlphp/modules/janus/tests/lib/CustomDictionaryLoaderTestResources/dictionaries
     */
    private function loadTranslations($filePath)
    {
        $dictonaryJson = file_get_contents($filePath);
        $translations = json_decode($dictonaryJson, true);

        return $translations;
    }

    /**
     * @param string $dictionaryName
     * @param string $translationName
     * @param array $translations
     */
    private function includeTranslation($dictionaryName, $translationName, array $translations)
    {
        if (!isset($translations[$this->languageCode])) {
            return;
        }

        $translation = $translations[$this->languageCode];
        $this->template->includeInlineTranslation(
            $this->createTagForTranslation($dictionaryName, $translationName),
            $translation
        );
    }

    /**
     * Creates a Simplesamlphp compatible translation tag
     *
     * @param string $dictonaryName
     * @param string $translationName
     * @return string
     */
    private function createTagForTranslation($dictonaryName, $translationName)
    {
        return '{janus:' . $dictonaryName . ':' . $translationName . '}';
    }
}
