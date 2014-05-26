<?php
/**
 * Alternative for classes which still used SimpleSamle_Configuration directly.
 *
 * Based on SimpleSamle_Configuration, contains only the methods which were used by Janus.
 */
class Config
{
    const REQUIRED_OPTION = '___REQUIRED_OPTION___';

    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
//        $container->

        /** @var \Symfony\Component\HttpKernel\Kernel $kernel */
            $kernel->getContainer()->getParameter()

//        /** @var Application */
//        $application->;
    }

    /**
     * Retrieve a configuration option set in config.php.
     *
     * @param $name  Name of the configuration option.
     * @param $default  Default value of the configuration option. This parameter will default to NULL if not
     *                  specified. This can be set to SimpleSAML_Configuration::REQUIRED_OPTION, which will
     *                  cause an exception to be thrown if the option isn't found.
     * @return  The configuration option with name $name, or $default if the option was not found.
     */
    public function getValue($name, $default = NULL)
    {
        /* Return the default value if the option is unset. */
        if (!array_key_exists($name, $this->configuration)) {
            if ($default === self::REQUIRED_OPTION) {
                throw new Exception($this->location . ': Could not retrieve the required option ' .
                    var_export($name, TRUE));
            }
            return $default;
        }

        return $this->configuration[$name];
    }

    /**
     * This function retrieves a string configuration option.
     *
     * An exception will be thrown if this option isn't a string, or if this option isn't found, and no
     * default value is given.
     *
     * @param $name  The name of the option.
     * @param $default  A default value which will be returned if the option isn't found. The option will be
     *                  required if this parameter isn't given. The default value can be any value, including
     *                  NULL.
     * @return  The option with the given name, or $default if the option isn't found and $default is specified.
     */
    public function getString($name, $default = self::REQUIRED_OPTION)
    {
        assert('is_string($name)');

        $ret = $this->getValue($name, $default);

        if ($ret === $default) {
            /* The option wasn't found, or it matches the default value. In any case, return
             * this value.
             */
            return $ret;
        }

        if (!is_string($ret)) {
            throw new Exception($this->location . ': The option ' . var_export($name, TRUE) .
                ' is not a valid string value.');
        }

        return $ret;
    }

    /**
     * This function retrieves a boolean configuration option.
     *
     * An exception will be thrown if this option isn't a boolean, or if this option isn't found, and no
     * default value is given.
     *
     * @param $name  The name of the option.
     * @param $default  A default value which will be returned if the option isn't found. The option will be
     *                  required if this parameter isn't given. The default value can be any value, including
     *                  NULL.
     * @return  The option with the given name, or $default if the option isn't found and $default is specified.
     */
    public function getBoolean($name, $default = self::REQUIRED_OPTION)
    {
        assert('is_string($name)');

        $ret = $this->getValue($name, $default);

        if ($ret === $default) {
            /* The option wasn't found, or it matches the default value. In any case, return
             * this value.
             */
            return $ret;
        }

        if (!is_bool($ret)) {
            throw new Exception($this->location . ': The option ' . var_export($name, TRUE) .
                ' is not a valid boolean value.');
        }

        return $ret;
    }

    /**
     * This function retrieves an array configuration option.
     *
     * An exception will be thrown if this option isn't an array, or if this option isn't found, and no
     * default value is given.
     *
     * @param string $name  The name of the option.
     * @param mixed$default  A default value which will be returned if the option isn't found. The option will be
     *                       required if this parameter isn't given. The default value can be any value, including
     *                       NULL.
     * @return mixed  The option with the given name, or $default if the option isn't found and $default is specified.
     */
    public function getArray($name, $default = self::REQUIRED_OPTION)
    {
        assert('is_string($name)');

        $ret = $this->getValue($name, $default);

        if ($ret === $default) {
            /* The option wasn't found, or it matches the default value. In any case, return
             * this value.
             */
            return $ret;
        }

        if (!is_array($ret)) {
            throw new Exception($this->location . ': The option ' . var_export($name, TRUE) .
                ' is not an array.');
        }

        return $ret;
    }

    /**
     * This function retrieves an integer configuration option.
     *
     * An exception will be thrown if this option isn't an integer, or if this option isn't found, and no
     * default value is given.
     *
     * @param $name  The name of the option.
     * @param $default  A default value which will be returned if the option isn't found. The option will be
     *                  required if this parameter isn't given. The default value can be any value, including
     *                  NULL.
     * @return  The option with the given name, or $default if the option isn't found and $default is specified.
     */
    public function getInteger($name, $default = self::REQUIRED_OPTION)
    {
        assert('is_string($name)');

        $ret = $this->getValue($name, $default);

        if ($ret === $default) {
            /* The option wasn't found, or it matches the default value. In any case, return
             * this value.
             */
            return $ret;
        }

        if (!is_int($ret)) {
            throw new Exception($this->location . ': The option ' . var_export($name, TRUE) .
                ' is not a valid integer value.');
        }

        return $ret;
    }

    /**
     * Check whether an key in the configuration exists...
     */
    public function hasValue($name)
    {
        return array_key_exists($name, $this->configuration);
    }
}
