<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ユーザ登録ページ</title>
        <link rel="stylesheet" href="../tamazon_css/tamazon_signup.css">
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
            <form method="post" action="register.php">
                <p>ユーザー名：<input type="text" name="user_name" placeholder="ユーザー名"></p>
                <p>パスワード：<input type="password" name="password" placeholder="パスワード"></p>
                <p><input type="submit" value="ユーザーを新規作成する"></p>
            </form>
            <p><a href="login.php">ログインページに戻る</a></p>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>