<main>
    <section>
        <header>
            <a href="https://mimikyu.info/">mimikyu.info</a>
            <h1>ひとこと掲示板</h1>
            <p>データベースの読み書きとページネーションのテスト</p>
            <p>よかったら書いてください😊</p>
            <br>
            <b>ソースコードはこちら</b>
            <p><a href="https://github.com/mimimiku778/mimikyu.info/blob/master/controllers/pages/BoardPageController.php">
                    コントローラー Github</a>
            </p>
            <p><a href="https://github.com/mimimiku778/mimikyu.info/blob/master/models/BoardModel.php">
                    データベースのモデル Github</a>
            </p>
            <p><a href="https://github.com/mimimiku778/mimikyu.info/blob/master/views/board/board.php">
                    ビュー Github</a>
            </p>
            <p><a href="https://github.com/mimimiku778/MimimalCMS">
                    これで作ってます Github</a>
            </p>
        </header>

        <!-- 送信フォーム -->
        <form action="/board" method="POST" id="hitokoto-form">
            <label for="hitokoto">
                <h2>なにかひとこと:</h2>
            </label>
            <input name="text" id="hitokoto" type="text" maxlength="100" autocomplete="off" />

            <!-- CSRFトークンをセットする -->
            <?php csrfField() ?>
            <button id="submit" type="submit" disabled>送信</button>

            <?php if ($v->isPosted) : ?>
                <sup>投稿しました！</sup>
            <?php endif ?>
        </form>
    </section>

    <!-- select要素ページネーション -->
    <?php if ($v->maxPage > 1) : ?>
        <section>
            <div class="pager-select" ontouchstart>
                <form>
                    <select id="selectPager">
                        <?php echo $v->__select ?>
                    </select>
                    <label for="selectPager"><?php echo $v->__label ?></span></label>
                </form>
            </div>
        </section>
    <?php endif ?>

    <!-- 投稿リスト -->
    <section>
        <?php foreach ($v->posts as $post) : ?>
            <article>
                <aside>
                    <small><?php echo $post['id'] ?>. <?php echo $post['time'] ?></small>
                    <p><?php echo $post['text'] ?></p>
                </aside>
            </article>
        <?php endforeach ?>
    </section>

    <!-- 次のページ・前のページボタン -->
    <?php if ($v->maxPage > 1) : ?>
        <nav class="search-pager">
            <?php if ($v->pageNumber > 1) : ?>
                <div class="button01 prev" ontouchstart>
                    <a href="<?php echo ($v->url)($v->pageNumber - 1) ?>"><?php echo $v->pageNumber - 1 ?>ページへ</a>
                </div>
            <?php endif ?>

            <span class="button01label"><?php echo $v->pageNumber . ' / ' . $v->maxPage ?></span>

            <?php if ($v->pageNumber < $v->maxPage) : ?>
                <div class="button01 next" ontouchstart>
                    <a href="<?php echo ($v->url)($v->pageNumber + 1) ?>">次のページへ</a>
                </div>
            <?php endif ?>
        </nav>
    <?php endif ?>

</main>
<script src="/js/functions.js"></script>
<script>
    // 入力に応じてボタンの disabled を切り替え
    const submitBtn = byId('submit')
    const input = byId('hitokoto')
    toggleButtonByInputValue(input, submitBtn)

    // 一部環境でボタンの disabled が効かないので、追加の処理を入れる
    const form = byId('hitokoto-form')
    form.addEventListener('submit', e => {
        if (submitBtn.disabled) {
            event.preventDefault()
        }
    });

    ((el) => {
        if (!el) return

        // ページ読み込み後にselect要素の選択をリセット
        window.addEventListener('pageshow', () => qS('form', el).reset())

        // selectを選択したときにvalueのURLに遷移する
        const select = qS('select', el)
        select.addEventListener('change', () => select.value && (location.href = select.value))
    })(qS('.pager-select'));
</script>