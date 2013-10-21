<?php
require_once dirname(__DIR__) . "/autoload.php";

class sspmod_janus_DiContainer extends Pimple
{
    const CONFIG = 'config';
    const METADATA_CONVERTER = 'metadata-converter';

    /** @var sspmod_janus_DiContainer */
    private static $instance;

    public function __construct()
    {
        $this->registerConfig();
        $this->registerMetadataConverter();
    }

    /**
     * @return sspmod_janus_DiContainer
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof sspmod_janus_DiContainer) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return SimpleSAML_Configuration
     */
    public function getConfig()
    {
        return $this[self::CONFIG];
    }

    protected function registerConfig()
    {
        $this[self::CONFIG] = $this->share(function (sspmod_janus_DiContainer $container)
        {
            $config = SimpleSAML_Configuration::getConfig('module_janus.php');
            return $config;
        });
    }

    /**
     * @return sspmod_janus_Metadata_Converter_Converter
     */
    public function getMetaDataConverter()
    {
        return $this[self::METADATA_CONVERTER];
    }

    protected function registerMetadataConverter()
    {
        $this[self::METADATA_CONVERTER] = $this->share(
            function (sspmod_janus_DiContainer $container)
            {
                $janusConfig = $container->getConfig();
                $metadataConverter = new sspmod_janus_Metadata_Converter_Converter();

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenValuesCommand());

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_FlattenKeysCommand());

                $metadataConverter->registerCommand(new sspmod_janus_Metadata_Converter_Command_ScopeConverterCommand());

                $mapping = $janusConfig->getArray('md.mapping', array());
                $mapKeysCommand = new sspmod_janus_Metadata_Converter_Command_MapKeysCommand();
                $mapKeysCommand->setMapping($mapping);
                $metadataConverter->registerCommand($mapKeysCommand);

                return $metadataConverter;
            }
        );
    }
}