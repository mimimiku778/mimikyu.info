<?php

declare(strict_types=1);

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

        // ビューに渡す値のオブジェクト
        $val = new stdClass();

        // ページの最大数を計算する
        $val->maxPage = calcMaxPages($totalRecords, self::NUMBER_ITEMS);

        // リクエストのページ番号を取得する
        $val->pageNumber = getQueryNum('page', 1);

        // リクエストのページ番号が最大ページ数を超える場合はリダイレクト
        if ($val->pageNumber > $val->maxPage) {
            redirect(self::PAGE_URL);
        }

        // ページネーションのselect要素を取得する
        // NOTE: Keyの頭が__で始まる場合はサニタイズ処理を通しません
        [$val->__select, $val->__label] = $this->selectPagenation(
            $val->pageNumber,
            $totalRecords,
            self::NUMBER_ITEMS,
            $val->maxPage,
            self::PAGE_URL
        );

        // ビューに渡すページネーションのURL
        $val->url = fn ($n) => genePagerUrl($n, self::PAGE_URL);

        // 投稿リストを取得する
        $val->posts = $this->model->get(
            ['offset' => calcOffset($val->pageNumber, self::NUMBER_ITEMS), 'limit' => self::NUMBER_ITEMS]
        );

        // 日付の形式を変換する
        foreach ($val->posts as &$post) {
            $post['time'] = $this->getDateTimeFormatted($post['time']);
        }

        // 投稿した後であるか
        $val->isPosted = getRemoveSessionValue('validPost');

        /** ビューに渡す
         *      オブジェクトか連想配列のみを渡せます。（それ以外は InvalidArgumentException が投げられます）
         *      連想配列の場合はテンプレートの中で Key = 変数名 になって展開されます。
         *      オブジェクトの場合は $v で渡されます。
         *      文字列はすべてサニタイズされます。key・プロパティ名の頭が "__" で始まる場合はサニタイズされません。
         */
        View::render('header', ['title' => 'ひとこと掲示板']);
        View::render('board/board', $val);
        View::render('footer');
        View::display();
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
     * 
     * @return array [$selectElement, $label]
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
            $selectElement .= $getElement(genePagerUrl($i, $url), $selected($i), $startNum($i), $endNum($i), $i) . "\n";
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
        $this->model->write(
            [
                'text' => Normalizer::normalize($_POST['text']),
                'ip' => ($_SERVER["REMOTE_ADDR"] ?? 'null'),
                'ua' => createUserLogStr()
            ]
        );

        // 投稿があったフラグをセッションにいれる
        $_SESSION['validPost'] = true;

        // 掲示板にリダイレクトする
        redirect(self::PAGE_URL, 303);
    }

    /**
     * POSTリクエストのバリデーション
     * 
     * @throws ValidationException CSRFトークンが無効な場合
     * @throws InvalidInputException 投稿の文字列が無効な場合
     *          上記の Exception は ExceptionHandler で捕捉されて、400 Bad requestを返すようになっています。
     * @throws ThrottleRequestsException
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

        // 連投を検出する
        if (!$this->model->limitInterval()) {
            throw new ThrottleRequestsException();
        }
    }
}
