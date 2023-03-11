<?php

class BoardPageController extends AbstractPageController
{
    private const NUMBER_ITEMS = 20;
    private BoardModel $model;

    /**
     *  https://mimikyu.info/board
     */
    public function index()
    {
        // 掲示板のモデル
        $this->model = new BoardModel;

        // POSTリクエストであるか
        if (isPostRequest()) {
            // 投稿を書き込む
            $this->post();
        }

        // レコード数を取得する
        $recordCount = $this->model->getRecordCount();

        // ページネーションの要素を取得する
        $__pager = $this->pagenation($recordCount);
        $__selectPager = $this->selectPagenation($recordCount, ...$__pager);

        // 投稿を何件目から取得するか計算する
        $offset = $__pager['num'] === 1 ? 0 : self::NUMBER_ITEMS * ($__pager['num'] - 1);

        // 投稿リストを取得する
        $posts = $this->model->get(['offset' => $offset, 'limit' => self::NUMBER_ITEMS]);

        // 日付の形式を変換する
        foreach ($posts as &$post) {
            $post['time'] = $this->getDateTimeFormatted($post['time']);
        }

        // 投稿した後であるか
        $__isPosted = getRemoveSessionValue('validPost');

        // ビューに渡す
        View::render('header', ['title' => 'ひとこと掲示板']);
        // NOTE: Keyの頭が__で始まる場合はサニタイズ処理を通しません
        View::render('board/board',  compact('posts', '__isPosted', '__pager', '__selectPager'));
        View::render('footer');
    }

    /**
     * POSTリクエストの処理
     * 
     * NOTE: プライベートメソッドはルーティングの対象から外れます
     */
    private function post()
    {
        // 有効なPOSTリクエストか検証する
        try {
            $this->validatePost();
        } catch (InvalidInputException | ValidationException $e) {
            throw new NotFoundException();
        }

        // ユーザーの情報を取得する
        $user = ($_SERVER["REMOTE_ADDR"] ?? '') . ': ' . ($_SERVER['HTTP_USER_AGENT'] ?? '');

        // 投稿をデータベースに書き込む
        $this->model->write(['text' => $_POST['text'], 'user' => $user]);

        // 投稿があったフラグをセッションにいれる
        $_SESSION['validPost'] = true;

        // 掲示板にリダイレクトする
        redirect('/board');
    }

    /**
     * POSTリクエストのバリデーション
     */
    private function validatePost()
    {
        // 有効なCSRFトークンが送られてきているか
        if (!verifyCsrfToken()) {
            throw new ValidationException();
        }

        // 送られてきたデータが有効であるか
        if (!validateKeyStr($_POST, 'text', 100)) {
            throw new InvalidInputException();
        }
    }

    /**
     *  前のページ・次のページボタンの値を取得
     */
    private function pagenation(int $recordCount): array
    {
        // ページの最大数を計算する
        $max = (int) ceil($recordCount / self::NUMBER_ITEMS);

        // リクエストのページ番号を取得
        $num = 1;
        if (validateKeyNum($_GET, 'page')) {
            if ($_GET['page'] > $max) {
                // ページ番号が最大数を超える場合はリダイレクト
                redirect('/board');
            } else {
                $num = (int) $_GET['page'];
            }
        }

        // Viewで使うページのURLを生成するコールバック関数
        $url = fn ($num) => "https://mimikyu.info/board/{$num}";

        return compact('max', 'num', 'url');
    }

    /**
     *  ページネーションのselect要素を生成
     */
    private function selectPagenation(int $recordCount, int $max, int $num, callable $url): array
    {
        // ページ番号の表示に必要な要素を取得する
        $element = fn ($url, $select, $i, $end, $n) => "<option value='{$url}' {$select}>{$n}ページ ({$i} - {$end}コメント)</option>";

        // 選択されたページに対して"selected"属性を返す
        $selected = fn ($n) => ($n === $num) ? "selected='selected'" : '';

        // ページ番号に応じて、そのページの最初のデータの番号を計算する
        $startNum = fn ($n) => ($n === 1) ? $recordCount : $recordCount - self::NUMBER_ITEMS * ($n - 1);

        // ページ番号に応じて、そのページの最後のデータの番号を計算する
        $endNum = fn ($n) => ($n === $max) ? 1 : $recordCount - self::NUMBER_ITEMS * ($n + 1);

        // 各ページ番号の要素を作成
        $html = '';
        for ($i = 1; $i <= $max; $i++) {
            $html .= $element($url($i), $selected($i), $startNum($i), $endNum($i), $i) . "\n";
        }

        // ラベルの番号を取得
        $labelStartNum = $startNum($num);
        $LabelEndNum = $endNum($num);

        // select要素のラベルを作成
        $label = "{$num}ページ ({$labelStartNum} - {$LabelEndNum}コメント)";

        return [$html, $label];
    }

    /**
     * MySQLの日付フォーマット「2023/03/11(土) 05:37:25」の形式に変換
     */
    private function getDateTimeFormatted(string $dateTimeString): string
    {
        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $weekDayIndex = (int) $dateTime->format('w');

        return $dateTime->format('Y/m/d') . '(' . $weekDays[$weekDayIndex] . ') ' . $dateTime->format('H:i:s');
    }
}
