<?php

require_once __DIR__ . '/View.php';

abstract class AbstractPageController
{
    public function __construct()
    {
        session_set_cookie_params([
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    abstract protected function index();
}