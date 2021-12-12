<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ログインページ</title>
        <link rel="stylesheet" href="../tamazon_css/tamazon_login.css">
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
        
<?php include 'template_msg.php'; ?>
            
            <form method="post">
                <p><input type="text" name="user_name" placeholder="ユーザー名"></p>
                <p><input type="password" name="password" placeholder="パスワード"></p>
                <p><input type="submit" value="ログイン"></p>
            </form>
            <p><a href="./signup.php">ユーザーの新規作成</a></p>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>
