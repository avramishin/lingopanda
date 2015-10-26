<?php
/**
 * Sends all notifications to a specified email.
 * 
 * Consider using this class together with Debug_ErrorHook_RemoveDupsWrapper
 * to avoid mail server flooding when a lot of errors arrives. 
 */

require_once sprintf("%s/Util.php", __DIR__);
require_once sprintf("%s/TextNotifier.php", __DIR__);

require_once sprintf('Zend/Mail.php');
require_once sprintf('Zend/Mail/Transport/Abstract.php');
require_once sprintf('%s/Zend/App/Mail/Transport/AmazonSES.php', LIBRARY_ROOT);

class Debug_ErrorHook_MailNotifier extends Debug_ErrorHook_TextNotifier
{
	private $_to;
	private $_charset;
	private $_whatToSend;
	private $_subjPrefix;
	
	public function __construct($to, $whatToSend, $subjPrefix = "[ERROR] ", $charset = "UTF-8")
	{
        parent::__construct($whatToSend);
		$this->_to = $to;
		$this->_subjPrefix = $subjPrefix;
		$this->_charset = $charset;
	}
	
    protected function _notifyText($subject, $body)
    {

        if(empty($this->_to)){
            # no recipients found, no messages
            return;
        }

        $mail = new Zend_Mail($this->_charset);
        $transport = new App_Mail_Transport_AmazonSES(
            array(
                'accessKey'  => cfg()->amazon->awsKey,
                'privateKey' => cfg()->amazon->awsSecret
            )
        );

        $mail->setFrom(cfg()->emailsFromAddress);

        foreach($this->_to as $address => $name){
            $mail->addTo($address, $name);
        }

        $mail->setSubject($subject);
        $mail->setBodyText($body);
        $mail->send($transport);
    }

    # Not used
    protected function _mail()
    {
    	$args = func_get_args();
    	@call_user_func_array("mail", $args);
    }
    
    private function _encodeMailHeader($header) 
    {
        return preg_replace_callback(
            '/((?:^|>)\s*)([^<>]*?[^\w\s.][^<>]*?)(\s*(?:<|$))/s',
            array(__CLASS__, '_encodeMailHeaderCallback'),
            $header
        );
    }

    private function _encodeMailHeaderCallback($p) 
    {
    	$encoding = $this->_charset;
        return $p[1] . "=?$encoding?B?" . base64_encode($p[2]) . "?=" . $p[3];
    }    
}