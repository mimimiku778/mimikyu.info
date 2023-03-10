<?php

class BoardPageController extends AbstractPageController
{
    public function index()
    {
        View::render('header', ['title' => 'ひとこと掲示板']);
        
        View::render('footer');
    }
}