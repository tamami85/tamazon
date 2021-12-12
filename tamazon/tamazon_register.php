<?php
$err_msg        = array();
$user_name      = '';
$ec_password    = '';
$date           = date('Y-m-d H:i:s');
$pattern        = '/^[0-9a-zA-Z]{6,}$/';

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
            } else if (preg_match($pattern, $user_name) !== 1) {
                $err_msg[] = 'ユーザー名は6文字以上の半角英数字じゃないとあかんで';
            }
        }
        if (isset($_POST['password']) === TRUE) {
            $ec_password = $_POST['password'];
            if ($ec_password === '') {
                $err_msg[] = 'パスワード入れてや';
            } else if (preg_match($pattern, $ec_password) !== 1) {
                $err_msg[] = 'パスワードは6文字以上の半角英数字じゃないとあかんで';
            }
        }
        
        $dbh->beginTransaction();
        if (count($err_msg) === 0) {//エラーなかったら
            try {//データベースに名前被りがないか聞いてみる
                $sql = 'SELECT
                            user_name
                        FROM
                            ec_user';
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($row as $value) {
                    if ($user_name === $value['user_name']) {//ユーザー名がかぶってへんか確認
                        $err_msg[] = 'そのユーザー名は使えへんで';
                    }
                }
                if (count($err_msg) === 0) {
                    $ec_password = password_hash($ec_password, PASSWORD_DEFAULT);//ハッシュドポテトして入れる
                    try {
                        $sql = 'INSERT INTO
                                    ec_user
                                    (user_name, password, create_datetime)
                                VALUES
                                    (?, ?, ?)';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $user_name,             PDO::PARAM_STR);
                        $stmt->bindValue(2, $ec_password,           PDO::PARAM_INT);
                        $stmt->bindValue(3, $date,                  PDO::PARAM_STR);
                        $stmt->execute();
                    } catch(PDOException $e) {
                        $err_msg[] = 'あなたの情報入らんかったわ'. $e;
                    }//データベースに登録
                }
                    
                $dbh->commit();
            } catch(PDOException $e) {
                $dbh->rollback();
                $err_msg[] = 'エラーあったしできひんかったわ';
            }//データベースに名前被りがないか聞いてみる
        }//エラーなかったら
    }//ポストされたら
} catch (PDOException $e) {
    $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
}//データベース接続
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ユーザー登録完了画面</title>
        <link rel="stylesheet" href="tamazon_css/tamazon_register.css">
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
<?php if (count($err_msg) > 0) { //エラーあるかないかでちゃう動き?>
        <ul>
    <?php foreach ($err_msg as $value) { ?>
            <li><img src="./decoration.img/15cat.png"><?php   print $value; ?><img src="./decoration.img/15cat.png"></li>
    <?php } ?>
        </ul>
        <p><a href="tamazon_signup.php">戻る</a></p>
        
<?php } else if (count($err_msg) === 0) { ?>
        <p>アカウント作成が完了しました</p>
        <p>ログインページからログインしてください！</p>
        <p><a href="tamazon_login.php">ログインページへGO！</a></p>
<?php } ?>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>