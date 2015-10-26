<?php

/**
 * Get configuration as object
 * @return stdClass
 */
function cfg() {
    static $_configObject = null;
    if($_configObject === null){
        # simplest way to make an object from assoc array :)
        $_configObject = json_decode(json_encode($GLOBALS['cfg']));
    }
    return $_configObject;
}

/**
 * Get DB Query object
 * @param string $configClass
 * @return DalMysqlQuery
 */
function db($inst = 'default') {
    global $defaultDb;
    static $db = array();
    if (!empty($defaultDb) && $inst == 'default') {
        $inst = $defaultDb;
    }

    //print_r($db);

    if (!isset($db[$inst])) {
        $dbs = cfg()->db;
        if (!isset($dbs->$inst)) {
            throw new Exception('Database config not found: ' . $inst);
        }
        $cfg = $dbs->$inst;
        $db[$inst] = new DalMysqlQuery($cfg);
    }

    return $db[$inst]();
}

function dbselect($inst = null) {
    global $defaultDb;
    $defaultDb = $inst;
}

/**
 * Get site url
 * @param string $path
 * @return string
 */
function site_url($path){
    return sprintf('http://%s/%s', $_SERVER['HTTP_HOST'], $path);
}

/**
 * Get Dklab_Realplexor
 * @return Dklab_Realplexor
 */
function rpl(){
    static $realplexor;

    if(!isset($realplexor)){
        $realplexor = new CommonRpl();
    }

    return $realplexor;
}

/**
 * Autoload class
 * @param string $className
 */
function autoload($className) {

    $classPath = array(
        INCLUDE_ROOT . "/classes/{$className}.php",
        INCLUDE_ROOT . "/classes/tables/{$className}.php"
    );

    if (preg_match('/^([A-Z][a-z]*)\w+/', $className, $m)) {
        $prefix = strtolower($m[1]);
        $classPath []= INCLUDE_ROOT . "/classes/{$prefix}/{$className}.php";
    }

    foreach($classPath as $class){
        if(file_exists($class)){
            include_once($class);
        }
    }
}

/**
 * Get request value and trim
 * @param string $param
 * @param mixed $default
 * @return mixed
 */
function r($param, $default = '') {
    if (isset($_REQUEST[$param]))
        return is_array($_REQUEST[$param]) ? $_REQUEST[$param] : trim($_REQUEST[$param]);
    return $default;
}

/**
 * Get session value and trim
 * @param string $param
 * @param mixed $default
 * @return mixed
 */
function s($param, $default = '') {
    if (isset($_SESSION[$param]))
        return is_array($_SESSION[$param]) ? $_SESSION[$param] : trim($_SESSION[$param]);
    return $default;
}

function newid() {
    return substr(bcadd(bcmul(mt_rand(), 4294967296), mt_rand()), 0, 16);
}


/**
 * Caching function. Checks if the key exists in the cache
 * if not - runs the function and caches the result.
 * So, next time it will be there :)
 * @param $function
 * @param $key
 * @param int $timeout
 * @return mixed
 */
function cache($function, $key, $timeout = 300){
    $result = mc($key);

    if(!$result){
        $result = $function();
        if(!empty($result)){
            mc($key, $result, $timeout);
        }
    }

    return $result;
}


/**
 * Get/set memcached value
 * @static var object $memcache
 * @param mixed $key
 * @param mixed $value if value is false then get value, else set value
 * @param int $timeout timeout in seconds
 * @return mixed value or false
 */
function mc($key, $value = false, $timeout = false) {
    static $memcache = null;


    if(!cfg()->memcache->active){
        return false;
    }

    if (!class_exists('Memcache')) {
        throw new Exception('No memcache - no fun');
    }

    if (!$memcache) {
        $memcache = new Memcache();
        if (!$memcache->connect(cfg()->memcache->host, cfg()->memcache->port)) {
            return '';
            # throw new RuntimeException('Memcache connect failed');
        }
    }

    if ($value === false) {
        return $memcache->get($key);
    } elseif ($value === null) {
        $memcache->delete($key, 0);
    } else {
        if (!$memcache->set($key, $value, false, $timeout ? $timeout : cfg()->memcache->timeout)) {
            # throw new RuntimeException('Memcache set failed');
        }
    }
}

/**
 * Return time in database format (Y-m-d H:i:s)
 * @param mixed $time Integer time or string date or 0 (for current time)
 * @return string
 */
function dbtime($time = false) {
    if (is_string($time)) {
        $time = strtotime($time);
    }
    return $time ? date('Y-m-d H:i:s', $time) : date('Y-m-d H:i:s');
}

/**
 * Return date in database format (Y-m-d)
 * @param mixed $time Integer time or string date or 0 (for current date)
 * @return string
 */
function dbdate($time = false) {
    if (is_string($time)) {
        $time = strtotime($time);
    }
    return $time ? date('Y-m-d', $time) : date('Y-m-d');
}

/**
 * Remove untranslated symbols
 *
 * @param string $string
 * @return string
 */
function unaccent($string) {
    if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
        $string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
    }
    return $string;
}

/**
 * Get text in A-Z a-z 0-9 characters range
 * @param string $str
 * @return string
 */
function azname($str) {
    $str = unaccent($str);
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $str = str_replace('&', 'and', $str);
    return preg_replace('#[^A-Za-z0-9\.\-]#', '_', $str);
}

/**
 * Escape SQL like special characters (escape character is \)
 * @param string $str
 * @return string
 */
function escape_like($str) {
    return str_replace('_', '\\_', str_replace('%', '\\%', str_replace('\\', '\\\\', $str)));
}

function fs_get_lock($name){
    $name = azname($name);
    $filename = "/tmp/{$name}.lock";

    fclose(fopen($filename, "a+b"));
    if(!$fp = fopen($filename, "r+b")){
        return false;
    }

    if($fr = flock($fp, LOCK_EX | LOCK_NB)){
        var_dump($fr);
        return $fp;
    }
    return false;
}

function fs_release_lock($fp){
    @flock($fp, LOCK_UN);
    @fclose($fp);
}

/**
 * Encrypt data by given $key, MCRYPT_RIJNDAEL_256
 * @param string $key
 * @param string $value
 * @return string
 */
function encrypt($key, $value)
{
    $text = $value;
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
    return $crypttext;
}

/**
 * Decrypt data by given $key, MCRYPT_RIJNDAEL_256
 * @param string $key
 * @param string $value
 * @return string
 */
function decrypt($key, $value)
{
    $crypttext = $value;
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
    return trim($decrypttext);
}

/**
 * Get associative array of objects
 * @param array $array Source array
 * @param string $field Field to use as key
 * @return array Result array
 */
function associate($array, $field) {
    $res = array();
    foreach ($array as $r) {
        if (!empty($r->$field)) {
            $res[$r->$field] = $r;
        }
    }
    return $res;
}

/**
 * Get array of fields from array of objects
 * @param array $array
 * @param string $field
 * @return array
 */
function column($array, $field) {
    $res = array();
    foreach ($array as $r) {
        if (!empty($r->$field)) {
            $res []= $r->$field;
        }
    }
    return $res;
}

function utf8_str_limit($s, $maxlength = 256, $continue = "\xe2\x80\xa6", &$is_cutted = null, $tail_min_length = 20)
{
    $is_cutted = false;
    if ($continue === null) $continue = "\xe2\x80\xa6";


    if (strlen($s) <= $maxlength) return $s;
    $s2 = str_replace("\r\n", '?', $s);
    $s2 = preg_replace('/&(?> [a-zA-Z][a-zA-Z\d]+
                            | \#(?> \d{1,4}
                                  | x[\da-fA-F]{2,4}
                                )
                          );
                        /sx', '?', $s2);

    if (strlen($s2) <= $maxlength || strlen(utf8_decode($s2)) <= $maxlength) return $s;


    preg_match_all('/(?> \r\n
                       | &(?> [a-zA-Z][a-zA-Z\d]+
                            | \#(?> \d{1,4}
                                  | x[\da-fA-F]{2,4}
                                )
                          );  # html ñóùíîñòè (&lt; &gt; &amp; &quot;)
                       | [\x09\x0A\x0D\x20-\x7E]           # ASCII
                       | [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
                       |  \xE0[\xA0-\xBF][\x80-\xBF]       # excluding overlongs
                       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                       |  \xED[\x80-\x9F][\x80-\xBF]       # excluding surrogates
                       |  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
                       | [\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
                       |  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
                     )
                    /sx', $s, $m);
    #d($m);
    if (count($m[0]) <= $maxlength) return $s;

    $left = implode('', array_slice($m[0], 0, $maxlength));

    $left2 = rtrim($left, "\x00..\x28\x2A..\x2F\x3A\x3C..\x3E\x40\x5B\x5C\x5E..\x60\x7B\x7C\x7E\x7F");
    if (strlen($left) !== strlen($left2)) $return = $left2 . $continue;
    else {
        $right = implode('', array_slice($m[0], $maxlength));
        preg_match('/^(?> [a-zA-Z\d\)\]\}\-\.:]+
                        | \xe2\x80[\x9d\x99]|\xc2\xbb|\xe2\x80\x9c  #çàêðûâàþùèå êàâû÷êè
                        | \xc3[\xa4\xa7\xb1\xb6\xbc\x84\x87\x91\x96\x9c]|\xc4[\x9f\xb1\x9e\xb0]|\xc5[\x9f\x9e]  #òóðåöêèå
                        | \xd0[\x90-\xbf\x81]|\xd1[\x80-\x8f\x91]   #ðóññêèå áóêâû
                        | \xd2[\x96\x97\xa2\xa3\xae\xaf\xba\xbb]|\xd3[\x98\x99\xa8\xa9]  #òàòàðñêèå
                      )+
                    /sx', $right, $m);
        $right = isset($m[0]) ? rtrim($m[0], '.-') : '';
        $return = $left . $right;
        if (strlen($return) !== strlen($s)) $return .= $continue;
    }
    $tail = substr($s, strlen($return));
    if (strlen(utf8_decode($tail)) < $tail_min_length) return $s;

    $is_cutted = true;
    return $return;
}


