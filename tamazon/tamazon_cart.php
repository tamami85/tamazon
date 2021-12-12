<?php
$img_dir        = './item_img/';
$err_msg        = array();
$scc_msg        = array();
$user_id        = '';
$item_id        = '';
$cal_result     = '';
$amount         = '';
$pattern        = '/^[0-9]*$/';
$date           = date('Y-m-d H:i:s');



//ここでログインしてるか確認
session_start();
if (isset($_SESSION['login_id'])) {
  $user_id = $_SESSION['login_id'];
} else {
  // 非ログインの場合、ログインページへリダイレクト
  header('Location: tamazon_login.php');
  exit;
}

//データベースアクセス
$host = 'localhost';//たまのパソコン
$username = 'codecamp45262';
$password = 'codecamp45262';
$dbname = 'codecamp45262';
$charset = 'utf8';

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    //user_name持ってくる
    try {
        $sql = 'SELECT
                    user_name
                FROM
                    ec_user
                WHERE
                    user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,      PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);//文字列だけ配列に入れる
        
        foreach ($rows as $value) {
            $user_name = $value['user_name'];
        }
    } catch (PDOException $e) {
        header('Location: tamazon_logout.php');
        exit;
    }//user_name持ってくる
    
    
    //amount変更する
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//ポストされたら
        if (isset($_POST['sql_kind']) === TRUE) {//sql_kindはあるんか
            $sql_kind = $_POST['sql_kind'];
            //買いたい個数変更
            if ($sql_kind === 'change_amount') {//change_amountボタン押したら
            
                if (isset($_POST['amount']) === TRUE) {//amountはありますか
                    $amount = $_POST['amount'];
                    if ($amount === '') {
                        $err_msg[] = '何個ほしいんかちゃんと書いてや';
                    } else if (preg_match($pattern, $amount) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }//amountはありますか
                if (isset($_POST['item_id']) === TRUE) {//相変わらず存在確認
                    $item_id = $_POST['item_id'];
                    if ($item_id === '') {
                        $err_msg[] = '商品が正しくありません';
                    } else if (preg_match($pattern, $item_id) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }
                if (count($err_msg) === 0) {//エラーがなかったらamountアプデ
                    try {
                        $sql = 'UPDATE ec_cart
                                SET amount = ?,
                                    update_datetime = ?
                                WHERE item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $amount,            PDO::PARAM_INT);
                        $stmt->bindValue(2, $date,              PDO::PARAM_STR);
                        $stmt->bindValue(3, $item_id,           PDO::PARAM_INT);
                        $stmt->execute();
                        //成功メッセージを用意
                        $scc_msg[] = '個数の更新完了イェア';
                    } catch (PDOException $e) {
                        $err_msg[] = '個数の更新できひんかった';
                        throw $e;
                    }
                }//エラーがなかったらamountアプデ終わり
            }//change_amountボタン押したら
            
            
            //買いたいもの消す    
            if ($sql_kind === 'delete') {//deleteボタン
                if (isset($_POST['delete_item']) === TRUE) {//delete_itemはあるんか
                    $delete_item = $_POST['delete_item'];
                }//delete_itemはあるんか
                if (isset($_POST['item_id']) === TRUE) {//相変わらず存在確認item_id
                    $item_id = $_POST['item_id'];
                    if ($item_id === '') {
                        $err_msg[] = '商品が正しくありません';
                    } else if (preg_match($pattern, $item_id) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }//相変わらず存在確認item_id終わり
                if (count($err_msg) === 0) {//エラーがなかったら消す
                    try {
                        $sql = 'DELETE FROM ec_cart
                                WHERE item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $item_id,          PDO::PARAM_INT);
                        $stmt->execute();

                        //成功メッセージを用意
                        $scc_msg[] = 'カートの中消しといたイェア';
                    } catch (PDOException $e) {
                        throw $e;
                    }
                }//エラーがなかったら消す
            }//deleteボタン
                
        }//sql_kindはあるんか
    }//ポストされたら

    
    //データベースからec_cart持ってくる
    try {
        $sql = 'SELECT
                    ec_cart.item_id,
                    ec_cart.amount,
                    ec_item_master.img,
                    ec_item_master.name,
                    ec_item_master.price,
                    ec_item_master.status,
                    ec_item_stock.stock
                FROM
                    ec_cart
                LEFT OUTER JOIN
                    ec_item_master
                ON
                    ec_cart.item_id = ec_item_master.item_id
                LEFT OUTER JOIN
                    ec_item_stock
                ON
                    ec_item_master.item_id = ec_item_stock.item_id
                WHERE
                    ec_cart.user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,      PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);//配列に入れる
// print_r($rows);
        $cal_result = 0;
        if (count($rows) === 0) {
            $err_msg[] = 'カート空やん';
        } else {
            foreach($rows as $value) {
               $cal_result = $cal_result + $value['price'] * $value['amount'];//合計出す
               
                if ($value['amount'] > $value['stock']) {
                   $err_msg[] = $value['name'] . 'は在庫少ないから買えへんわ';
                } else if ($value['status'] !== 1) {
                    $err_msg[] = $value['name'] . 'は売られへんわ';
                }
            }
        }
        
    } catch (PDOException $e) {
        $err_msg[] = 'データ持ってこれんかった';
    }//データベースからec_cart持ってくる

    
} catch (PDOException $e) {
    echo '接続できませんでした。理由：'.$e->getMessage();
}




?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>tamazonのカート</title>
        <link rel="stylesheet" href="tamazon_css/tamazon_cart.css">
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
                <div class="flex">
                    <p>welcome!&nbsp;<?php print $user_name; ?></p>
                    <a href="tamazon_cart.php"><img src="./decoration.img/48cart.png"></a>
                </div>
                <a href="tamazon_logout.php"><p>logout</p></a>
        </header>
        <h2>これ買う<span class="blue">&#063;</span></h2>
        <ul>
<?php foreach ($err_msg as $value) { //エラーメッセージ表示?>
            <li><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
<?php foreach ($scc_msg as $value) { //成功ーメッセージ表示?>
            <li class="blue"><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
        </ul>
        <artcile>
<?php foreach ($rows as $value) { ?>
            <div><img class="img_size" src="<?php print htmlspecialchars($img_dir . $value['img'], ENT_QUOTES, 'UTF-8'); ?>"></div>
            <div><?php print htmlspecialchars($value['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div>￥<?php print htmlspecialchars($value['price'], ENT_QUOTES, 'UTF-8'); ?></div>
            <form method="post">
                <input type="text" name="amount" value="<?php print htmlspecialchars($value['amount'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="submit" value='変更'>
                <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="sql_kind" value="change_amount">
            </form>
            <form method="post">
                <input type="submit" name="delete_cart" value="削除">
                <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="sql_kind" value="delete">
            </form>
<?php } ?>
            <p>合計￥<?php print htmlspecialchars($cal_result, ENT_QUOTES, 'UTF-8'); ?></p> 
            <form method="post" action="tamazon_result.php">
                <input type="submit" name="buy" value="買う！">
            </form>
        </artcile>
        <p><a href="tamazon_home.php">戻る</a></p>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>