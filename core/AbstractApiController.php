<?php

abstract class AbstractApiController
{
    public function __construct()
    {
        $jsonData = file_get_contents('php://input');
        if (!is_string($jsonData) || empty(trim($jsonData))) {
            return;
        }

        $json = json_decode($jsonData, true);
        if (is_array($json)) {
            $_POST = $json;
            return;
        }
    }

    abstract public function index();
}
