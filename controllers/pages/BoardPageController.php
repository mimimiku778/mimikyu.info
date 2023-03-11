<?php

class BoardPageController extends AbstractPageController
{
    private const NUMBER_ITEMS = 10;
    public const PAGE_URL = 'https://mimikyu.info/board';

    private BoardModel $model;

    /**
     * https://mimikyu.info/board
     */
    public function index()
    {
        // 掲示板のモデル
        $this->model = new BoardModel;

        // POSTリクエストであるか
        if (isPostRequest()) {
            $this->post();
        }

        // GETクエリのバリデーション
        $this->validateGetQuery();

        // レコード数を取得する
        $totalRecords = $this->model->getRecordCount();

        // ページの最大数を計算する
        $maxPage = calcMaxPages($totalRecords, self::NUMBER_ITEMS);

        // リクエストのページ番号を取得する
        $pageNumber = getQueryNumValue('page', 1);

        // リクエストのページ番号が最大ページ数を超える場合はリダイレクト
        if ($pageNumber > $maxPage) {
            redirect(self::PAGE_URL);
        }

        // ページネーションのselect要素を取得する
        $__selectPager = $this->selectPagenation($pageNumber, $totalRecords, self::NUMBER_ITEMS, $maxPage, self::PAGE_URL);

        // ビューに渡すページネーションの値
        $__pager = compact('maxPage', 'pageNumber') + ['url' => self::PAGE_URL];

        // 投稿リストを取得する
        $posts = $this->model->get(['offset' => calcOffset($pageNumber, self::NUMBER_ITEMS), 'limit' => self::NUMBER_ITEMS]);

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
     * GETクエリのバリデーション
     *      任意のルートパラメーターが設定されている場合、どんなパス名でもアクセスができます。
     *      SEO的な観点から、有効なパラメーター以外は明示的にNot foundを返すように定義します。
     * 
     *      index.phpの Route::run の引数から任意のルートパラメータを設定できます。
     *      * **例** `Route::run('board/{page}')`
     * 
     *      NOTE: プライベートメソッドはルーティングの対象から外れます
     * 
     * @throws NotFoundException URLに無効なパラメータが含まれる場合
     */
    private function validateGetQuery()
    {
        // キーは存在するが、無効な値の場合はNot foundを返す
        validateKeyNum($_GET, 'page', minValue: 1, e: 'NotFoundException');
    }

    /**
     * 降順ページネーションのselect要素を生成
     */
    private function selectPagenation(int $pageNumber, int $totalRecords, int $itemsPerPage, int $maxPage, string $url): array
    {
        // ページ番号の表示に必要な要素を取得する
        $getElement = fn ($url, $selected, $start, $end, $i) => "<option value='{$url}' {$selected}>{$i}ページ ({$start} - {$end}コメント)</option>";

        // 選択されたページに対して"selected"属性を返す
        $selected = fn ($i) => ($i === $pageNumber) ? "selected='selected'" : '';

        // ページ番号に応じて、そのページの最初のインデックスを計算する
        $startNum = fn ($i) => calcDescRecordIndex($i, $totalRecords, $itemsPerPage);

        // ページ番号に応じて、そのページの最後のインデックスを計算する
        $endNum = fn ($i) => calcDescRecordIndex($i, $totalRecords, $itemsPerPage, $maxPage);

        // 各ページ番号の要素を生成する
        $selectElement = '';
        for ($i = 1; $i <= $maxPage; $i++) {
            $selectElement .= $getElement(generatePagerUrl($i, $url), $selected($i), $startNum($i), $endNum($i), $i) . "\n";
        }

        // ラベルの番号を取得する
        $labelStartNum = $startNum($pageNumber);
        $LabelEndNum = $endNum($pageNumber);

        // select要素のラベルを生成する
        $label = "{$pageNumber}ページ ({$labelStartNum} - {$LabelEndNum}コメント)";

        return [$selectElement, $label];
    }

    /**
     * MySQLの日付フォーマットを「2023/03/11(土) 05:37:25」の形式に変換
     */
    private function getDateTimeFormatted(string $dateTimeString): string
    {
        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeString);
        $weekDayIndex = (int) $dateTime->format('w');

        return $dateTime->format('Y/m/d') . '(' . $weekDays[$weekDayIndex] . ') ' . $dateTime->format('H:i:s');
    }

    /**
     * POSTリクエストの処理
     */
    private function post()
    {
        // 有効なPOSTリクエストか検証する
        $this->validatePostRequest();

        // 投稿をデータベースに書き込む
        $this->model->write(['text' => $_POST['text'], 'user' => createUserLogStr()]);

        // 投稿があったフラグをセッションにいれる
        $_SESSION['validPost'] = true;

        // 掲示板にリダイレクトする
        redirect(self::PAGE_URL);
    }

    /**
     * POSTリクエストのバリデーション
     * 
     * @throws ValidationException CSRFトークンが無効な場合
     * @throws InvalidInputException 投稿の文字列が無効な場合
     *      上記の Exception は ExeptionHandler で捕捉されて、400 Bad requestを返すようになっています。
     */
    private function validatePostRequest()
    {
        // 有効なCSRFトークンが送られてきているか
        if (!verifyCsrfToken()) {
            throw new ValidationException('無効なCSRFトークン');
        }

        // 送られてきた文字列が「存在する」、「空か空白スペースのみでない」、「100文字以下」であるか
        if (!validateKeyStr($_POST, 'text', 100)) {
            throw new InvalidInputException('無効な文字列');
        }
    }
}
