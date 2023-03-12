<?php

require_once __DIR__ . '/View.php';

abstract class AbstractPageController
{
    public function __construct()
    {
        session_start();
    }

    abstract protected function index();
}