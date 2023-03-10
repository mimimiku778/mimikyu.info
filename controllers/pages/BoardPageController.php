<?php

class BoardPageController extends AbstractPageController
{
    private const NUMBER_ITEMS = 20;

    /**
     *  https://mimikyu.info/board
     */
    public function index()
    {
        // 投稿した後であるか
        $isPosted = getRemoveSessionValue('validPost');

        // 掲示板のモデル
        $model = new BoardModel;
        // 投稿を取得する
        $posts = $model->get(['offset' => 0, 'limit' => 100]);

        // 日付の形式を変換する
        foreach ($posts as &$post) {
            $post['time'] = $this->getDateTimeFormatted($post['time']);
        }

        // ビューに渡す
        View::render('header', ['title' => 'ひとこと掲示板']);
        View::render('board/board',  compact('posts', 'isPosted'));
        View::render('footer');
    }

    /**
     *  NOTE: プライベートメソッドはルーティングの対象から外れます。
     */
    private function getDateTimeFormatted(string $dateTimeString): string
    {
        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $weekDayIndex = (int) $dateTime->format('w');

        return $dateTime->format('Y/m/d') . '(' . $weekDays[$weekDayIndex] . ') ' . $dateTime->format('H:i:s');
    }

    /**
     *  https://mimikyu.info/board/post
     */
    public function post()
    {
        // POSTリクエストであるか
        if (!isPostRequest()) {
            redirect('/board');
        }

        // 有効なPOSTリクエストか検証する
        try {
            $this->validatePost();
        } catch (InvalidInputException | ValidationException $e) {
            throw new NotFoundException();
        }

        // ユーザーの情報を取得する
        $user = ($_SERVER["REMOTE_ADDR"] ?? '') . ': ' . ($_SERVER['HTTP_USER_AGENT'] ?? '');

        // 掲示板のモデル
        $model = new BoardModel;
        // 投稿をデータベースに書き込む
        $model->write(['text' => $_POST['text'], 'user' => $user]);

        // 投稿があったフラグをセッションにいれる
        $_SESSION['validPost'] = true;

        // 掲示板にリダイレクトする
        redirect('/board');
    }

    private function validatePost()
    {
        // 有効なCSRFトークンが送られてきているか
        if (!VerifyCsrfToken()) {
            throw new ValidationException();
        }

        // 送られてきたデータが有効であるか
        if (!validateKeyStr($_POST, 'text', 100)) {
            throw new InvalidInputException();
        }
    }
}
