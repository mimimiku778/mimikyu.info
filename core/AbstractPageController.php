<?php

require_once __DIR__ . '/View.php';

abstract class AbstractPageController
{
    public function __construct()
    {
        session_start();
        register_shutdown_function(fn () => View::display());
    }

    abstract protected function index();
}
