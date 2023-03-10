<section>
    <header>
        <h1>ひとこと掲示板</h1>
        <p>データベースの読み書きとページネーションのテスト</p>
        <br>
        <p><a href="https://github.com/mimimiku778/mimikyu.info/blob/master/controllers/pages/BoardPageController.php">
                ソースコードはこちらGithub</a>
        </p>
    </header>
    <form action="/board" method="POST">
        <label for="hitokoto">なにかひとこと:</label>
        <input id="hitokoto" type="text" maxlength="100" />
        <button type="submit">送信</button>
        <?php if ($isPosted) : ?>
            <sup>投稿しました！</sup>
        <?php endif ?>
    </form>
</section>

<section>
    <?php foreach ($posts as $post) : ?>
        <article>
            <aside>
                <small><?php echo $post['id'] ?>. 2022/12/11(金) 12:01:32</small>
                <p><?php echo $post['text'] ?></p>
            </aside>
        </article>
    <?php endforeach ?>
</section>