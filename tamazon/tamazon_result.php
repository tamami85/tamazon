<?php

$img_dir        = './item_img/';
$err_msg        = array();
$scc_msg        = array();
$user_name      = '';
$user_id        = '';
$item_id        = '';
$cal_result     = '';
$amount         = '';
$stock          = '';
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
$host           = 'localhost';//たまのパソコン
$username       = 'codecamp45262';
$password       = 'codecamp45262';
$dbname         = 'codecamp45262';
$charset        = 'utf8';

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
    
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//ポストされたら
        if (isset($_POST['buy']) === TRUE) {//buyはあるんか
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
//print_r($rows);
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
        }//buyはあるんか
    }//ポストされたら
    

    $dbh->beginTransaction();
    if (count($err_msg) === 0) {
        try {
            foreach($rows as $value) {
                $amount     = $value['amount'];
                $item_id    = $value['item_id'];
                $sql = 'UPDATE ec_item_stock
                        SET stock = stock - ?,
                            update_datetime = ?
                        WHERE item_id = ?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $amount,            PDO::PARAM_INT);
                $stmt->bindValue(2, $date,              PDO::PARAM_STR);
                $stmt->bindValue(3, $item_id,           PDO::PARAM_INT);
                $stmt->execute();
            }

            $sql = 'DELETE FROM ec_cart
                    WHERE user_id = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $user_id,       PDO::PARAM_INT);
            $stmt->execute();
            //print $item_id;
            $dbh->commit();
            //成功メッセージを用意
            $scc_msg[] = '買ってくれてありがと';
        } catch (PDOException $e) {
            $dbh->rollback();
            throw $e;
        }
    }


} catch (PDOException $e) {
    echo '接続できませんでした。理由：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>tamazon_result</title>
    <link rel="stylesheet" href="tamazon_css/tamazon_result.css">
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
    
    <ul>
<?php foreach ($err_msg as $value) { //エラーメッセージ表示?>
        <li><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
<?php foreach ($scc_msg as $value) { //エラーメッセージ表示?>
        <li class="blue"><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
    </ul>

    <article>
<?php if (count($rows) !== 0) { ?>
        <h2>Thank you so much<span class="gray">!</span></h2>
<?php } ?>
<?php foreach ($rows as $value) { ?>
        <div><img class="img_size" src="<?php print htmlspecialchars($img_dir . $value['img'], ENT_QUOTES, 'UTF-8'); ?>"></div>
        <div><?php print htmlspecialchars($value['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div>￥<?php print htmlspecialchars($value['price'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div>個数：<?php print htmlspecialchars($value['amount'], ENT_QUOTES, 'UTF-8'); ?></div>
<?php } ?>
        <p>合計￥<?php print htmlspecialchars($cal_result, ENT_QUOTES, 'UTF-8'); ?></p>
    </article>
    <p><a href="tamazon_home.php">homeに戻る</a></p>
    <footer id="footer">
        <p>Copyright &copy; tamazon All Rights Reserved.</p>
    </footer>
</body>
</html>