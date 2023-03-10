<?php

require_once __DIR__ . '/View.php';

abstract class AbstractPageController
{
    public function __construct()
    {
        register_shutdown_function(fn () => View::display());
    }

    abstract public function index();
}
