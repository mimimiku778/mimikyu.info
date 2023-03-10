<?php

class BoardPageController extends AbstractPageController
{
    private const NUMBER_ITEMS = 20;

    public function index()
    {
        $model = new BoardModel;
        $posts = $model->get(['offset' => 0, 'limit' => 100]);

        $isPosted = false;

        View::render('header', ['title' => 'ひとこと掲示板']);
        View::render('board/board', ['posts' => $posts, 'isPosted' => $isPosted]);
        View::render('footer');
    }

    public function isPostRequest()
    {

    }
}