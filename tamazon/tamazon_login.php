<?php
$err_msg = array();
$user_name = '';
$ec_password = '';
$user_id = '';



$host           = 'localhost';//たまのパソコン
$username       = 'codecamp45262';
$password       = 'codecamp45262';
$dbname         = 'codecamp45262';
$charset        = 'utf8';

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {//データベース接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//ポストされたら
    
        if (isset($_POST['user_name']) === TRUE) {
            $user_name = $_POST['user_name'];
            if ($user_name === '') {
                $err_msg[] = 'ユーザー名入れてや';
            }
        }
        if (isset($_POST['password']) === TRUE) {
            $ec_password = $_POST['password'];
        if ($ec_password === '') {
                $err_msg[] = 'パスワード入れてや';
            }
        }

        if (count($err_msg) === 0) {//エラーなかったら
            try {
                $sql = 'SELECT user_id, password
                        FROM ec_user
                        WHERE user_name = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_name,         PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
//print_r($row);
                if (count($row) === 0) {
                    $err_msg[] = 'ユーザー名が一致せんぞ';
                }
                foreach ($row as $value) {
                    $user_id = $value['user_id'];
                        //　↓ハッシュドポテトしたパスワードを入力したパスワードと比べる
                    if (password_verify($ec_password, $value['password'])) {
                        session_start();
                        //ほんでTRUEやったらセッションに入れたげる
                        $_SESSION['login_id'] = $user_id;
                        header('Location: tamazon_home.php');//tamazon_homeに行く
                        //print 'ログインが成功した！おめ！';
                        
                    } else {
                        $err_msg[] = 'パスワードちゃうやん';
                    }
                }
            } catch(PDOException $e) {
                $err_msg[] = '処理できひんかった' . $e;
            }
        }//エラーなかったら
    }//ポストされたら

} catch (PDOException $e) {
    $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
}//データベース接続

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ログインページ</title>
        <link rel="stylesheet" href="tamazon_css/tamazon_login.css">
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
            <h1>tamazon<img src="./decoration.img/cat_hand.png"></h1>
        </header>
            <ul>
<?php foreach ($err_msg as $value) { ?>
                <li><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
            </ul>
            <form method="post">
                <p><input type="text" name="user_name" placeholder="ユーザー名"></p>
                <p><input type="password" name="password" placeholder="パスワード"></p>
                <p><input type="submit" value="ログイン"></p>
            </form>
            <p><a href="./tamazon_signup.php">ユーザーの新規作成</a></p>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>
