<style>
    body {
        max-width: 600px;
        margin: 0 auto;
    }
</style>
<section>
    <header>
        <h1>ひとこと掲示板</h1>
        <p>データベースの読み書きとページネーションのテスト</p>
        <br>
        <p><a href="https://github.com/mimimiku778/mimikyu.info/blob/master/controllers/pages/BoardPageController.php">
                ソースコードはこちらGithub</a>
        </p>
    </header>
    <form action="/board/post" method="POST">
        <label for="hitokoto">なにかひとこと:</label>
        <input name="text" id="hitokoto" type="text" maxlength="100" />

        <!-- CSRFトークンをセットする -->
        <?php csrfField() ?>

        <button id="submit" type="submit" disabled>送信</button>
        <?php if ($isPosted) : ?>
            <sup>投稿しました！</sup>
        <?php endif ?>
    </form>
</section>

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
<script src="/js/functions.js"></script>
<script>
    toggleButtonByInputValue(byId('hitokoto'), byId('submit'))
</script>