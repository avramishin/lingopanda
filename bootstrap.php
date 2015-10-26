<?php
error_reporting(E_ALL);

define('ROOT', __DIR__);
define('INCLUDE_ROOT', sprintf('%s/include', ROOT));
define('LIBRARY_ROOT', sprintf('%s/include/library', ROOT));

set_time_limit(0);

# Handle shitty configs
require sprintf('%s/config.php', ROOT);
$cfgDefault = $cfg;
require sprintf('%s/config.local.php', ROOT);
foreach ($cfgDefault as $k => $v) {
    if (!isset($cfg[$k])) {
        $cfg[$k] = $v;
    }
}

require sprintf('%s/include/functions/common.php', ROOT);

spl_autoload_register('autoload');
date_default_timezone_set(cfg()->timezone);

require_once sprintf('%s/Debug/ErrorHook/Listener.php', LIBRARY_ROOT);
$errorsToMail = new Debug_ErrorHook_Listener();
$errorsToMail->addNotifier(
    new Debug_ErrorHook_RemoveDupsWrapper(
        new Debug_ErrorHook_RssNotifier(
            Debug_ErrorHook_TextNotifier::LOG_ALL
        ),
        cfg()->tmpDir, // lock directory
        300 // do not resend the same error within 300 seconds
    )
);