<?php
require_once 'Duration/Parser.php';

/**
 * Converter for xs:duration to unix timestamp.
 *
 * Note this only converts from an xs:duration to a Unix timestamp, NOT vice versa... yet
 */ 
class sspmod_janus_Xml_Duration
{
    /**
     * @var int
     */
    protected $_seconds = 0;

    /**
     * @static
     * @param int $seconds
     * @return sspmod_janus_Xml_Duration
     */
    public static function createFromUnixTime($seconds)
    {
        return new self($seconds);
    }

    /**
     * For an xs:duration value, parse it and return a Duration object.
     *
     * @static
     * @param string   $duration xs:duration value
     * @param null|int $fromTime From when? By default uses the current time.
     * @return sspmod_janus_Xml_Duration
     */
    public static function createFromDuration($duration, $fromTime = null)
    {
        $parser = new sspmod_janus_Xml_Duration_Parser($duration, $fromTime = null);
        $parser->parse();
        return new self($parser->getSeconds());
    }

    /**
     * @param int $seconds
     */
    protected function __construct($seconds)
    {
        $this->_seconds = $seconds;
    }

    /**
     * Unix timestamp.
     *
     * @return int
     */
    public function getSeconds()
    {
        return $this->_seconds;
    }
}
