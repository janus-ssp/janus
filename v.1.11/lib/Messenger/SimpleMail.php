<?php
class sspmod_janus_Messenger_SimpleMail extends sspmod_janus_Messenger
{
    private $_config = null;

    private $_headers;

    protected function __construct(array $option)
    {
        $this->_config = SimpleSAML_Configuration::getConfig('module_janus.php');
        $this->_headers = $option['headers'];
    }

    public function send(array $data) 
    {
        $user = new sspmod_janus_User($this->_config);
        $user->setUid($data['uid']);
        $user->load();
        $to = $user->getEmail();

        $subject = '[JANUS] ' . $data['subject'];
        $body = $data['message'];

        if(!mail($to, $subject, $body, $this->_headers)) {
            throw new Exception('Could not send mail - ' . var_export($data, true));
        }

        return true;
    }
}
