<?php

class TestPageController extends AbstractPageController
{
    public function index()
    {
        var_dump(isset($_GET['amin']));
        echo 'hello world';
    }
}
