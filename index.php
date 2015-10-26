<?php

ob_start("ob_gzhandler");

$sessionless = array(
    '/users/prompt',
    '/tracking/click',
    '/tracking/c.gif',
    '/tracking/view',
    '/tracking/v.gif',
    '/rpl',
    '/rtc',
    '/portals/adtemplates/templates-grid',
    '/portals/adtemplates/values-grid',
    '/portals/adtemplates/checkers',
);

try {

    require_once sprintf('%s/bootstrap.php', __DIR__);
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


    if (!in_array($url, $sessionless)) {
        # require_once sprintf('%s/include/functions/sessions.php', ROOT);
        session_start();
    }

    $controllersDir = sprintf('%s/controllers', __DIR__);

    $controllers = array(
        sprintf('%s/%s.php', $controllersDir, $url),
        sprintf('%s/%sindex.php', $controllersDir, $url),
        sprintf('%s/%s/index.php', $controllersDir, $url)
    );

    $controllerFound = false;
    foreach ($controllers as $controller) {
        if (file_exists($controller)) {
            $fp = @fopen('/tmp/cashout.log', 'a');
            fwrite($fp, date('Y-m-d H:i:s') . " {$_SERVER['REMOTE_ADDR']} {$url}\n");
            # fwrite($fp, print_r($_REQUEST, true));
            require $controller;
            fwrite($fp, date('Y-m-d H:i:s') . " {$_SERVER['REMOTE_ADDR']} END -----------------------------------------------\n\n");
            fclose($fp);
            $controllerFound = true;
            break;
        }
    }

    if (!$controllerFound) {
        throw new Exception(sprintf('%s not found', $url));
    }


} catch (Exception $e) {
    printf("<pre>%s\n%s</pre>", $e->getMessage(), $e->getTraceAsString());
    flush();
}