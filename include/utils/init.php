#!/usr/bin/env php
<?php

if (!function_exists('readline')) {
    function readline($prompt = '') {
        echo $prompt;
        return rtrim( fgets( STDIN ), "\n" );
    }
}

function press_any_key() {
    echo "\nPress any key to continue or Control-C to break execution now\n";
    `read -sn 1`;
    echo "\n";
}

function yes($question = 'Continue?') {
    echo "\n{$question} (y/n)\n";
    $answer = trim(readline());
    return preg_match('/^y/i', $answer);
}

function process_template($template, $filename) {
    ob_start();
    require $template;
    $data = ob_get_clean();
    file_put_contents($filename, $data);
    echo "Created $filename\n";
}

function db_error($mysql) {
    if (mysqli_error($mysql)) {
        exit('Database error: ' . mysqli_error($mysql) . "\n");
    }
}

function h2($msg) {
    echo "\n$msg\n\n";
}

define('MAIN_ROOT', realpath(__DIR__ . '/../../'));
$config_local = MAIN_ROOT . '/config.local.php';

// Check user

$user = trim(`whoami`);

if ($user == 'root') {
    h2('ERROR: you are running this script as root');
    exit(1);
}

//if (file_exists($config_local)) {
//    $emsg = h2('WARNING: config_local.php is already exists');
//}

h2('This script will create configuration file and copy database structure from existing installation');

h2('Checking environment');
$badEnvironment = false;

echo 'Curl available: ';
if (function_exists('curl_version')) {
    echo "yes\n";
} else {
    $badEnvironment = true;
    echo "NO (curl module required)\n";
}

echo 'Memcache available: ';
if (class_exists('Memcache')) {
    echo "yes\n";
} else {
    $badEnvironment = true;
    echo "NO (memcache module required)\n";
}

echo 'Mcrypt available: ';
if (function_exists('mcrypt_encrypt')) {
    echo "yes\n";
} else {
    $badEnvironment = true;
    echo "NO (mcrypt module required)\n";
}

echo 'Mysqli available: ';
if (function_exists('mysqli_connect')) {
    echo "yes\n";
} else {
    $badEnvironment = true;
    echo "NO (mysqli module required)!\n";
}

echo 'Also make use apache mod_rewrite is enabled\n';

if ($badEnvironment) {
    exit("Bad environment\n");
}

h2('Configuring server');

$host = readline('Enter server host: ');
$rpl_host = $host;
//$rpl_host = readline('Enter rpl host: ');
$email = readline('Enter your email (for error reports): ');
$name = readline('Enter your name (for error reports): ');

h2('Configuring database access');

$db_host = readline('Enter database host: ');
$db_user = readline('Enter database user name: ');
$db_password = readline('Enter database user password: ');

$databases = array(
    'adwords',
    'api',
    'bing',
    'common',
    'priceportal',
    'stats'
);

try {
    $mysql = @mysqli_connect($db_host, $db_user, $db_password);
    if (!$mysql) {
        exit("Mysql connection failed\n");
    }

    $sourceHost = readline('Enter host source for database structure: ');

    if (strpos($sourceHost, 'http://') !== 0) {
        $sourceHost = 'http://' . $sourceHost;
    }

    h2('Fetching database structure');

    $createSql = array();
    foreach ($databases as $db) {
        $answer = file_get_contents("$sourceHost/export-db?dbconfig=$db");
        if (!$answer || !@json_decode($answer)) {
            exit("Error reading data\n");
        }
        $response = json_decode($answer);
        if ($response->error) {
            exit("{$response->error}\n");
        }
        $createSql[$db] = $response->tablesSql;
    }

    h2('Creating databases');

    foreach ($databases as $db) {
        $dbName = in_array($db, array('api', 'priceportal')) ? "runashop_$db" : $db;
        $res = mysqli_query($mysql, "SHOW DATABASES LIKE '{$dbName}'");
        db_error($mysql);
        $array = mysqli_fetch_array($res);
        if (!$array) {
            echo("No database {$dbName}, creating...\n");
            $res = mysqli_query($mysql, "CREATE DATABASE `{$dbName}`");
            db_error($mysql);
        } else {
            echo("Database {$dbName} found\n");
        }
        echo "Creating tables...\n";
        $res = mysqli_query($mysql, "USE {$dbName}");
        db_error($mysql);
        foreach ($createSql[$db] as $query) {
            $res = mysqli_query($mysql, $query);
            db_error($mysql);
        }
    }

    h2('Writing configuration file');

    $replace = array(
        '%db_host%' => $db_host,
        '%db_user%' => $db_user,
        '%db_password%' => $db_password,
        '%host%' => $host,
        '%rpl_host%' => $rpl_host,
        '%email%' => $email,
        '%name%' => $name,
    );

    $template = file_get_contents(__DIR__ . '/init/config_local.phtml');
    $config = str_replace(array_keys($replace), array_values($replace), $template);

    $configName = MAIN_ROOT . '/config.local.generated.php';

    file_put_contents($configName, $config);

    echo "File saved as $configName. Rename it to config.local.php\n";

    if (!file_exists(MAIN_ROOT . '/tmp')) mkdir(MAIN_ROOT . '/tmp');
    if (!file_exists(MAIN_ROOT . '/public')) mkdir(MAIN_ROOT . '/public');

} catch (Exception $e) {
    echo "\nError: ";
    echo $e->getMessage();
    echo "\n";
}
