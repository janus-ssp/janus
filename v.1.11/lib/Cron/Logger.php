<?php

/**
 * Utility class to store log messages, optionally with 'namespace'.
 */ 
class sspmod_janus_Cron_Logger
{
    protected $_notices  = array();
    protected $_warnings = array();
    protected $_errors   = array();
    protected $_namespaces = array();

    public function __construct()
    {
    }

    /**
     * Store a namespace for the next log message.
     *
     * @example $logger->with($userId)->with($sessionId)->warn($message);
     *
     * @param string $namespace
     * @return sspmod_janus_Cron_Logger
     */
    public function with($namespace)
    {
        $this->_namespaces[] = $namespace;
        return $this;
    }

    /**
     * Log a notice, notices are informational messages or minor complaints.
     *
     * @param string $message
     * @return sspmod_janus_Cron_Logger
     */
    public function notice($message)
    {
        $this->_notices[$message] = array(
            'message' => $message,
            'namespaces' => $this->_namespaces,
        );
        $this->_namespaces = array();
        return $this;
    }

    /**
     * Log a warning, warnings are conditions that need to be resolved, but don't need immediate action.
     *
     * @param string $message
     * @return sspmod_janus_Cron_Logger
     */
    public function warn($message)
    {
        $this->_warnings[] = array(
            'message' => $message,
            'namespaces' => $this->_namespaces,
        );
        $this->_namespaces = array();
        return $this;
    }

    /**
     * Log an error, errors are conditions that require human intervention.
     *
     * @param $message
     * @return sspmod_janus_Cron_Logger
     */
    public function error($message)
    {
        $this->_errors[] = array(
            'message' => $message,
            'namespaces' => $this->_namespaces,
        );
        $this->_namespaces = array();
        return $this;
    }

    /**
     * @return bool 
     */
    public function hasWarnings()
    {
        return !empty($this->_warnings);
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    /**
     * @return array Notices in format array(0 => array(message => "message", namespaces => array(0=>"one",1=>"two")))
     */
    public function getNotices()
    {
        return $this->_notices;
    }

    /**
     * @return array Notices in format array("ns1" => array("n2" => "message"))
     */
    public function getNamespacedNotices()
    {
        return $this->_namespaceMessages($this->_notices);
    }

    /**
     * @return array Warnings in format array(0 => array(message => "message", namespaces => array(0=>"one",1=>"two")))
     */
    public function getWarnings()
    {
        return $this->_warnings;
    }

    /**
     * @return array Warnings in format array("ns1" => array("n2" => "message"))
     */
    public function getNamespacedWarnings()
    {
        return $this->_namespaceMessages($this->_warnings);
    }

    /**
     * @return array Errors in format array(0 => array(message => "message", namespaces => array(0=>"one",1=>"two")))
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @return array Errors in format array("ns1" => array("n2" => "message"))
     */
    public function getNamespacedErrors()
    {
        return $this->_namespaceMessages($this->_errors);
    }

    /**
     * Convert 'flat' messages to 'namespaced' messages.
     *
     * Example:
     * array(array(message=>"", namespaces=>array("ns1","ns2")))
     * =>
     * array(ns1=>array(ns2=>"message"))
     *
     * @param array $messages
     * @return array Namespace messages
     */
    protected function _namespaceMessages($messages)
    {
        $namespacedMessages = array();
        foreach ($messages as $message) {
            $pointer = &$namespacedMessages;
            if (empty($message['namespaces'])) {
                $pointer[] = $message['message'];
                continue;
            }

            foreach ($message['namespaces'] as $namespace) {
                if (!isset($pointer[$namespace])) {
                    $pointer[$namespace] = array();
                }
                $pointer = &$pointer[$namespace];
            }
            $pointer[] = $message['message'];
        }
        return $namespacedMessages;
    }

    /**
     * Get lines meant for SimpleSAMLphp Cron Summary.
     *
     * @return array
     */
    public function getSummaryLines()
    {
        $summaryLines = array();
        $messagesCollection = array(
            "Error"  => $this->_errors,
            "Warning"=> $this->_warnings,
            "Notice" => $this->_notices,
        );
        foreach ($messagesCollection as $label => $messages) {
            foreach ($messages as $message) {
                $summaryLine = "&lt;$label&gt;";
                foreach ($message['namespaces'] as $namespace) {
                    $summaryLine .= "&lt;$namespace&gt;";
                }
                $summaryLine .= ' ' . $message['message'];
                $summaryLines[] = $summaryLine;
            }
        }
        return $summaryLines;
    }
}
