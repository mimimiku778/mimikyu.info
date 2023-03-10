<?php

class IndexPageController extends AbstractPageController
{
    public function index()
    {
        View::render('header');
        View::render('toppage');
        View::render('footer');
    }
}