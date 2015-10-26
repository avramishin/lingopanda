<?php

class Mutex {
    var $writablePath = '';
    var $lockName = '';
    var $fileHandle = null;

    public function __construct($lockName, $writablePath = '/tmp'){
        $this->lockName = preg_replace('/[^a-zA-Z0-9\.\-\_]/', '', $lockName);
        $this->writablePath = $writablePath;
    }

    public function lock(){
        if(!$this->fileHandle){
            $this->fileHandle = @fopen($this->getLockFilePath(), 'a+');
            if($this->fileHandle){
                if(flock($this->fileHandle, LOCK_EX | LOCK_NB)) {
                    return $this->fileHandle;
                } else {
                    fclose($this->fileHandle);
                    return false;
                }
            } else {
                return false;
            }
        }
        return $this->fileHandle;
    }

    public function release(){
        if (!$this->fileHandle) {
            return false;
        }
        $success = fclose($this->fileHandle);
        $filePath = $this->getLockFilePath();
        if (file_exists($filePath)) unlink($filePath);
        $this->fileHandle = null;
        return $success;
    }

    public function getLockFilePath(){
        return $this->writablePath . DIRECTORY_SEPARATOR . $this->lockName;
    }

    public function isLocked() {
        if ($this->fileHandle) return $this->fileHandle;
        $this->fileHandle = @fopen($this->getLockFilePath(), 'a+');
        if($this->fileHandle){
            if(flock($this->fileHandle, LOCK_EX | LOCK_NB)){
                $this->release();
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

}