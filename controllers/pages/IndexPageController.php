<?php

class IndexPageController extends AbstractPageController
{
    public function index()
    {
        View::render('header', ['title' => 'ふわふわポートフォリオ😵']);
        View::render('toppage');
        View::render('footer');
    }
}