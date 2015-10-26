<?php

/**
 * Represents single log file
 */
class Log {

    var $fileName;
    var $stderr = false;
    var $stdout = false;
    var $includeTime = false;

    /**
     * Create log
     * @param $fileName
     */
    function __construct($fileName) {
        $this->fileName = $fileName;
    }

    /**
     * Write line to log
     * @param $line
     * @return mixed
     * @throws Exception
     */
    function line($line) {
        if ($this->stderr) {
            error_log("$line");
        }
        if ($this->stdout) {
            echo("$line\n");
        }
        $fp = @fopen($this->fileName, 'a');
        if (!$fp) {
            throw new Exception("Write to log file {$this->fileName} failed");
        }
        $text = $line . "\n";
        if ($this->includeTime) $text = dbtime() . ' ' . $text;
        fwrite($fp, $text);
        fclose($fp);
        return $line;
    }

    /**
     * Remove log file
     */
    function truncate() {
        if (is_writable($this->fileName)) {
            unlink($this->fileName);
        }
    }

    /**
     * Get log with specified name
     * @param $name
     * @return Log
     */
    static function get($name) {
        $log = new Log(ROOT . '/logs/' . $name . '.txt');
        return $log;
    }

    /**
     * Write to debug log
     * @param $line
     */
    static function debug($line) {
        static $debugLog;
        if (!$debugLog) {
            $debugLog = Log::get('debug');
            $debugLog->includeTime = true;
        }
        $bt =  debug_backtrace();
        $line = $bt[0]['file'] . ' (' . $bt[0]['line'] . '): ' . $line;
        $debugLog->line($line);
    }

}