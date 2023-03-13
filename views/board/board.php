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
        <form class="hitokoto-form" action="/board" method="POST">
            <label for="hitokoto">
                <h2>なにかひとこと:</h2>
            </label>
            <input name="text" id="hitokoto" type="text" maxlength="100" autocomplete="off" />

            <!-- CSRFトークンをセットする -->
            <?php csrfField() ?>
            <button type="submit" disabled>送信</button>

            <?php if ($v->isPosted) : ?>
                <sup>投稿しました！</sup>
            <?php endif ?>
        </form>
    </section>
    <section>
        <!-- select要素ページネーション -->
        <?php if ($v->maxPage > 1) : ?>
            <nav class="pager-select" ontouchstart>
                <form>
                    <select id="selectPager">
                        <?php echo $v->__select ?>
                    </select>
                    <label for="selectPager"><?php echo $v->__label ?></span></label>
                </form>
            </nav>
        <?php endif ?>

        <!-- 投稿リスト -->
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
    ((el) => {
        if (!el) return

        // 入力に応じてボタンの disabled を切り替え
        const input = qS('input', el)
        const submit = qS('[type="submit"]', el)
        input.addEventListener('input', () => {
            submit.disabled = !validateStringNotEmpty(input.value)
        })

        // 一部環境でボタンの disabled が効かないので、追加の処理を入れる
        el.addEventListener('submit', e => {
            submit.disabled && e.preventDefault()
        })
    })(qS('.hitokoto-form'));

    ((el) => {
        if (!el) return

        // selectを選択したときにvalueのURLに遷移する
        const select = qS('select', el)
        select.addEventListener('change', () => {
            select.value && (location.href = select.value)
        })
    })(qS('.pager-select'));
</script>