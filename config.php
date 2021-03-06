<?php
$cfg = array(

    'db' => array(
        'lingopanda' => array(
            'host' => 'localhost',
            'user' => 'lingopanda',
            'pass' => '',
            'name' => 'lingopanda'
        ),
    ),

    'host' => 'api-lingopanda.makers.do',
    
    'memcache' => array(
        'active' => false,
        'timeout' => 20,
        'host' => 'localhost',
        'port' => 11211
    ),

    'encryptionKey' => '',

    'debug' => false,
    'publicDir' => sprintf('%s/public', __DIR__),
    'logsDir' => sprintf('%s/logs', __DIR__),
    'tmpDir' => sprintf('%s/tmp', __DIR__),
    'timezone' => 'Europe/Berlin'
);