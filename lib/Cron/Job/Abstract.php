<?php

abstract class sspmod_janus_Cron_Job_Abstract implements sspmod_janus_Cron_Job_Interface
{
    protected function _mailTechnicalContact($tag, sspmod_janus_Cron_Logger $logger)
    {
        $errorHtml   = $this->_getHtmlForMessages($logger->getNamespacedErrors(), 'errors');
        $warningHtml = $this->_getHtmlForMessages($logger->getNamespacedWarnings(), 'warnings');
        $noticeHtml  = $this->_getHtmlForMessages($logger->getNamespacedNotices(), 'notices');

        $config = SimpleSAML_Configuration::getInstance();
        $time = date(DATE_RFC822);
        $url = SimpleSAML_Utilities::selfURL();

        $message = <<<MESSAGE
<h1>Cron report</h1>
<p>Cron ran at $time</p>
<p>URL: <tt>$url</tt></p>
<p>Tag: $tag</p>
<h2>Errors</h2>
$errorHtml
<h2>Warnings</h2>
$warningHtml
<h2>Notices</h2>
$noticeHtml
MESSAGE;

        $toAddress = $config->getString('technicalcontact_email', 'na@example.org');
        if ($toAddress == 'na@example.org') {
            SimpleSAML_Logger::error('Cron - Could not send email. [technicalcontact_email] not set in config.');
        } else {
            $email = new SimpleSAML_XHTML_EMail($toAddress, 'JANUS cron report', 'no-reply@example.edu');
            $email->setBody($message);
            $email->send();
        }
    }

    /**
     * Notifies managing contact about updated metadata of entity
     *
     * @param   sspmod_janus_Entity $entity
     * @param   string $metadataXml
     * @return void
     */
    protected function _mailUpdatedMetaData(sspmod_janus_Entity $entity, $metadataXml) {
        $config = SimpleSAML_Configuration::getInstance();
        $time = date(DATE_RFC822);
        $entityName = $entity->getPrettyname();
        $entityId   = $entity->getEntityId();

        $message = <<<MESSAGE
<h1>Metadata Change detected</h1>
<p>Cron ran at $time</p>
<p>Name: $entityName</p>
<p>EntityId: $entityId</p>
MESSAGE;

        $toAddress = $config->getString('managingcontact_email');
        if (empty($toAddress)) {
            SimpleSAML_Logger::error('Cron - Could not send email. [managingcontact_email] not set in config.');
        }

        $fromAddress = 'no-reply@surfnet.nl';
        $subject = "Metadata Change detected for entity " . $entity->getPrettyname() . " (" . $entity->getEntityId() . "])";
        $email = new SimpleSAML_XHTML_EMail($toAddress, $subject, $fromAddress);
        $email->setBody($message);

        // Add gzipped metadata
        $attachmentContent  = gzencode($metadataXml);
        $attachmentFileName = 'metadata-' . $entityName . '.xml.gz';
        $email->addAttachment($attachmentContent, $attachmentFileName, 'application/zip');

        $email->send();
    }

    protected function _getHtmlForMessages($messages, $type)
    {
        if (count($messages) > 0) {
            $messageHtml = '<ul>';
            foreach ($messages as $label => $message) {
                $messageHtml .= '<li>';
                if (is_array($message)) {
                    $messageHtml .= $this->_getListForMessages($message, $label);
                }
                else {
                    $messageHtml .= $message;
                }
                $messageHtml .= "</li>";
            }
            $messageHtml .= '</ul>';
        }
        else {
            $messageHtml = "<p>No $type</p>";
        }
        return $messageHtml;
    }

    protected function _getListForMessages($messages, $label)
    {
        $html = "<dl><dt>$label</dt>";
        foreach ($messages as $label => $message) {
            if (is_array($message)) {
                $html .= "<dd>" . $this->_getListForMessages($message, $label) . "</dd>";
            }
            else {
                $html .= "<dd>$message</dd>";
            }
        }
        $html .= "</dl>";
        return $html;
    }
}