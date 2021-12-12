<?php
$err_msg        = array();//エラーメッセージの配列も空で用意
$scc_msg        = array();
$rows           = array();
$img_dir        = './item_img/';
$pattern_4      = '/^[0-4]*$/';
$date           = date('Y-m-d H:i:s');
$new_place      = '';
$user_name      = '';
$sql_kind       = '';
$item_id        = '';
$user_id        = '';


session_start();
if (isset($_SESSION['login_id'])) {
  $user_id = $_SESSION['login_id'];
} else {
  // 非ログインの場合、ログインページへリダイレクト
  header('Location: tamazon_login.php');
  exit;
}


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
    
    try {//user_nameとる
        $sql = 'SELECT
                    user_name
                FROM
                    ec_user
                WHERE
                    user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id,      PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);//文字列だけ配列に入れる
        
        foreach ($row as $value) {
            $user_name = $value['user_name'];
        }
    } catch (PDOException $e) {
        header('Location: tamazon_logout.php');
        exit;
    }//user_nameとる
    
    
    //検索ボタン押されたら
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//ポストされたら
    
        if (isset($_POST['sql_kind']) === TRUE) {//sql_kindボタン押されたら
            $sql_kind = $_POST['sql_kind'];
            if ($sql_kind === 'insert_cart') {//$sql_kindがinsert_cartやったら
                
                if (isset($_POST['item_id']) === TRUE) {//item_idはありますか
                    $item_id = $_POST['item_id'];
                    if ($item_id === '') {
                        $err_msg[] = '何も買ってへんで';
                    }
                    if ($user_id === '') {
                        $err_msg[] = 'いや誰やねん';
                    }
                }//item_idはありますか
// print $user_id;
// print $item_id;
                if(count($err_msg) === 0) {//エラーなかったらカートに商品がある？
                    try {//カートに同一商品があるか確認する
                        $sql = 'SELECT
                                    amount
                                FROM
                                    ec_cart
                                WHERE
                                    user_id = ?
                                AND
                                    item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $user_id,      PDO::PARAM_INT);
                        $stmt->bindValue(2, $item_id,      PDO::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
// print_r($row);
                        if (count($row) > 0) {
                            try {
                                $sql = 'UPDATE ec_cart
                                        SET amount = amount + 1
                                        WHERE user_id = ?
                                        AND item_id = ?';
                                $stmt = $dbh->prepare($sql);
                                $stmt->bindValue(1, $user_id,               PDO::PARAM_INT);
                                $stmt->bindValue(2, $item_id,               PDO::PARAM_INT);
                                $stmt->execute();
                                
                                $scc_msg[] = 'カートに追加しといたで';
                            } catch(PDOException $e) {
                                $err_msg[] = 'カートに足せへんかった';
                            }
                            
                        } else {
                            try {
                                $sql = 'INSERT INTO 
                                            ec_cart
                                            (user_id, item_id, amount, create_datetime)
                                        VALUES
                                            (?, ?, ?, ?)';
                                $stmt = $dbh->prepare($sql);
                                $stmt->bindValue(1, $user_id,               PDO::PARAM_INT);
                                $stmt->bindValue(2, $item_id,               PDO::PARAM_INT);
                                $stmt->bindValue(3, 1,                      PDO::PARAM_INT);
                                $stmt->bindValue(4, $date,                  PDO::PARAM_STR);
                                $stmt->execute();

                                $scc_msg[] = 'カートに追加しといたで';
                            } catch(PDOException $e) {
                                $err_msg[] = 'カートに追加できひんかった';
                            }
                        }
                    } catch (PDOException $e) {
                        $err_msg[] = 'カートに同一商品があるか確認できひんかった';
                    }//カートに同一商品があるか確認する
                }//エラーなかったらカートに商品がある？
            }//$sql_kindがinsert_cartやった
        }//sql_kindボタン押されたら
        
        //検索結果表示
        if (isset($_POST['place_to_use']) === TRUE) {
            if (isset($_POST['new_place']) === TRUE) {
                $new_place = $_POST['new_place'];
                if ($new_place === '') {
                    $err_msg[] = 'どこで使うんか入力されてへんで';
                } else if (preg_match($pattern_4, $new_place) !== 1) {//整数かどうかチェック
                    $err_msg[] = '待ってこれどこで使うん？ちゃんと数字入れてや';
                }
        
                if(count($err_msg) === 0) {//エラーなかったら
                    try {//どっかで使う商品だけ取得
                        $sql = 'SELECT
                                    ec_item_master.item_id,
                                    ec_item_master.img,
                                    ec_item_master.name,
                                    ec_item_master.price,
                                    ec_item_stock.stock
                                FROM
                                    ec_item_master
                                LEFT OUTER JOIN
                                    ec_item_stock
                                ON
                                    ec_item_master.item_id = ec_item_stock.item_id
                                WHERE
                                    ec_item_master.place = ?
                                AND
                                    ec_item_master.status = 1';//使う商品だけ取得
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $new_place,      PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);//文字列だけ配列に入れる
//print_r($rows);
                    } catch (PDOException $e) {
                        $err_msg[] = '持ってこれんかった。'.$e->getMessage();
                    }//使う商品だけ取得
                }//エラーなかったら
            }//place_to_useがポストされたら
        }//new_placeがポストされたら
    }//ポストされたら
    
    
} catch (PDOException $e) {
    $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
}//データベース接続
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>tamazon_search</title>
    <link rel="stylesheet" href="tamazon_css/tamazon_search.css">
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
    
    <article>
        <ul>
<?php foreach ($err_msg as $value) { ?>
            <li><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
<?php foreach ($scc_msg as $value) { //成功ーメッセージ表示?>
            <li class="blue"><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
        </ul>
    
        <h2>SEARCH<span class="blue">&#046;&#046;&#046;</span></h2>
            <form method="post">
                <select name="new_place" required>
                    <option value="">選択してください</option>
                    <option value="0">kitchen</option>
                    <option value="1">bathroom&restroom</option>
                    <option value="2">bedroom</option>
                    <option value="3">outdoor</option>
                    <option value="4">others</option>
                </select>
                <input type="submit" name="place_to_use" value="SEARCH!">
            </form>
            
        <div class="flex_container">
<?php foreach ($rows as $value) { //ここからforeach?>
            <div class="container">
        <div><img class="img_size" src="<?php print htmlspecialchars($img_dir . $value['img'], ENT_QUOTES, 'UTF-8'); ?>"></div>
        <div><?php print htmlspecialchars($value['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div>￥<?php print htmlspecialchars($value['price'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php if ($value['stock'] > 0) {//ストックがあったらカートボタン ?>
        <form method="post">
            <input type="submit" value="カートに追加">
            <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="sql_kind" value="insert_cart">
        </form>
    <?php } else { //ストックなかったら売り切れ表示?>
            <div class="glay">売り切れ</div>
    <?php } ?>
            </div>
<?php } //ここまでforeach?>
        </div>
        
        <div><a href="tamazon_cart.php"><input type="submit" value="買い物かごへGO"></a></div>
        <div><a href="tamazon_home.php">homeに戻る</a></div>
    </article>
    <footer id="footer">
        <p>Copyright &copy; tamazon All Rights Reserved.</p>
    </footer>
</body>
</html>