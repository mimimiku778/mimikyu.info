<?php

class IndexPageController extends AbstractPageController
{
    public function index()
    {
        include __DIR__ . '/../../views/indexView.php';
    }
}