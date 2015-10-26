<?php

class JsonResponse
{

    public $success = true;
    public $error = false;
    public $data = array();

    function assertMethod($method)
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != strtolower($method)) {
            $this->error('Method not allowed');
        }
    }

    function error($msg = 'general error')
    {
        $this->success = false;
        $this->error = $msg;
        $this->send();
    }

    function send($exit = true)
    {
        header('Content-Type: application/json');
        echo json_encode($this);
        flush();
        if ($exit) {
            exit();
        }
    }

}