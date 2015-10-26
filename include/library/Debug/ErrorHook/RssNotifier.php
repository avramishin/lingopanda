<?php
/**
 * Sends all notifications to a specified email.
 *
 * Consider using this class together with Debug_ErrorHook_RemoveDupsWrapper
 * to avoid mail server flooding when a lot of errors arrives.
 */

require_once sprintf("%s/Util.php", __DIR__);
require_once sprintf("%s/TextNotifier.php", __DIR__);

class Debug_ErrorHook_RssNotifier extends Debug_ErrorHook_TextNotifier
{

    public function __construct($whatToSend)
    {
        parent::__construct($whatToSend);
    }

    protected function _notifyText($subject, $body)
    {
        $url = "http://avramishin.com/rss/submit.php";

        $args = http_build_query(array(
                'subject' => $subject,
                'body' => $body
            )
        );
        @file_get_contents($url . '?' . $args);
    }
}
