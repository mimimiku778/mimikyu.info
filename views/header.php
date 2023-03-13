<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?php echo $title ?></title>
    <meta name="description" content="わたしの個人的なWEB開発のポートフォリオとソースコードを公開しているので、ぜひチェックしてみてくださいね！メインはJavaScriptとPHPでがんばっています♪ 細かいところまでこだわっているので、ぜひ見てみてください！" />
    <link rel="stylesheet" href="/../assets/mvp.css">

    <?php if (isset($__css)) : ?>
        <?php foreach ($__css as $css) : ?>
            <?php echo '<link rel="stylesheet" href="/../assets/' . $css . '.css">' ?>
        <?php endforeach ?>
    <?php endif ?>
    
    <link rel="icon" type="image/png" href="/../assets/favicon.png">
</head>

<body>