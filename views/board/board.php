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
        <form action="/board" method="POST">
            <label for="hitokoto">
                <h2>なにかひとこと:</h2>
            </label>
            <input name="text" id="hitokoto" type="text" maxlength="100" autocomplete="off"/>

            <!-- CSRFトークンをセットする -->
            <?php csrfField() ?>
            <button id="submit" type="submit" disabled>送信</button>

            <?php if ($__isPosted) : ?>
                <sup>投稿しました！</sup>
            <?php endif ?>
        </form>
    </section>

    <!-- select要素ページネーション -->
    <?php if ($__pager['max'] > 1) : ?>
        <section>
            <div class="pager-select">
                <form>
                    <select id="selectPager">
                        <?php echo $__selectPager[0] ?>
                    </select>
                    <label for="selectPager"><?php echo $__selectPager[1] ?></span></label>
                </form>
            </div>
        </section>
    <?php endif ?>

    <!-- 投稿リスト -->
    <section>
        <?php foreach ($posts as $post) : ?>
            <article>
                <aside>
                    <small><?php echo $post['id'] ?>. <?php echo $post['time'] ?></small>
                    <p><?php echo $post['text'] ?></p>
                </aside>
            </article>
        <?php endforeach ?>
    </section>

    <!-- 次のページ・前のページボタン -->
    <?php if ($__pager['max'] > 1) : ?>
        <nav class="search-pager">
            <?php if ($__pager['num'] > 1) : ?>
                <div class="button01 prev">
                    <a href="<?php echo $__pager['url']($__pager['num'] - 1) ?>"><?php echo $__pager['num'] - 1 ?>ページへ</a>
                </div>
            <?php endif ?>

            <span class="button01label"><?php echo $__pager['num'] . ' / ' . $__pager['max'] ?></span>

            <?php if ($__pager['num'] < $__pager['max']) : ?>
                <div class="button01 next">
                    <a href="<?php echo $__pager['url']($__pager['num'] + 1) ?>">次のページへ</a>
                </div>
            <?php endif ?>
        </nav>
    <?php endif ?>

</main>
<script src="/js/functions.js"></script>
<script>
    toggleButtonByInputValue(byId('hitokoto'), byId('submit'));

    ((el) => {
        if (!el) return
        window.addEventListener('pageshow', () => qS('form', el).reset())
        const select = qS('select', el)
        select.addEventListener('change', () => select.value && (location.href = select.value))
    })(qS('.pager-select'));
</script>