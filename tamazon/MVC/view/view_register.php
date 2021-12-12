<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ユーザー登録完了画面</title>
        <link rel="stylesheet" href="../tamazon_css/tamazon_register.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            $(function(){
                var $ftr = $('#footer');
                if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
                    $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
                }
            });
        </script>
    </head>
    <body>
        <header>
            <h1>tamazon<img src="../decoration.img/cat_hand.png"></h1>
        </header>
<?php if (has_error() === true) { //エラーあるかないかでちゃう動き?>

<?php include 'template_msg.php'; ?>

        <p><a href="signup.php">戻る</a></p>
        
<?php } else if (has_error() === false) { ?>
        <p>アカウント作成が完了しました</p>
        <p>ログインページからログインしてください！</p>
        <p><a href="login.php">ログインページへGO！</a></p>
<?php } ?>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>