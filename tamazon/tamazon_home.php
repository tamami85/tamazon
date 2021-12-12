<?php
$img_dir        = './item_img/';
$err_msg        = array();
$scc_msg        = array();
$user_name      = '';
$ec_password    = '';
$date           = date('Y-m-d H:i:s');
$pattern        = '/^[0-9a-zA-Z]{6,}$/';
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

//print_r($_SESSION['login_id']);

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
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);//文字列だけ配列に入れる
        foreach ($row as $value) {
            $user_name = $value['user_name'];
        }
    } catch (PDOException $e) {
        header('Location: tamazon_logout.php');
        exit;
    }
    
    
    //ここからはカートに入れるボタン押したらの処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
                                    AND item_id = ?';
                            $stmt = $dbh->prepare($sql);
                            $stmt->bindValue(1, $user_id,      PDO::PARAM_INT);
                            $stmt->bindValue(2, $item_id,      PDO::PARAM_INT);
                            $stmt->execute();
                            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
//print_r($row);
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
                                } catch(PDOException $e) {//例外（エラー）があったらさかのぼって教えてくれる
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
                                    $stmt->bindValue(1, $user_id,               PDO::PARAM_INT);//はてなの中に値を入れるパラメータは数値型
                                    $stmt->bindValue(2, $item_id,               PDO::PARAM_INT);
                                    $stmt->bindValue(3, 1,                      PDO::PARAM_INT);
                                    $stmt->bindValue(4, $date,                  PDO::PARAM_STR);//はてなの中に値を入れるパラメータは文字列型
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
                }//item_idはありますか
            }//$sql_kindがinsert_cartやった
        }//sql_kindボタン押されたら
    }

    //ここからは商品ひっぱってくるやつ
    try {
        $sql = 'SELECT
                    ec_item_master.item_id,
                    ec_item_master.img,
                    ec_item_master.name,
                    ec_item_master.price,
                    ec_item_master.place,
                    ec_item_stock.stock
                FROM
                    ec_item_master
                LEFT OUTER JOIN
                    ec_item_stock
                ON
                    ec_item_master.item_id = ec_item_stock.item_id
                WHERE
                    ec_item_master.status = 1';//公開の商品だけ取得
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);//文字列だけ配列に入れる
//print_r($rows);
    } catch (PDOException $e) {
        $err_msg[] = '表示できませんでした。'.$e->getMessage();
    }

} catch (PDOException $e) {
    $err_msg[] = '接続できませんでした。理由：'.$e->getMessage();
}//データベース接続

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>tamami shopでお買い物</title>
        <link rel="stylesheet" href="tamazon_css/tamazon_home.css">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
        <!--↓グーグルからジャバスクのjquery呼び出し-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <!--↓ネット上にあるスライドをしてくれるファイルを呼び出す-->
        <script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script>
        <script type="text/javascript">
                $(document).ready(function(){
                    // ↓スライダーを定義
                    $('.slider').bxSlider({
                        // ↓5秒ごとにスライドする
                        auto: true,
                        pause: 5000,
                    });
                });
        </script>
        <script type="text/javascript">
            $(function(){
                // ↓フッターに入れる変数を定義
                var $ftr = $('#footer');
                // ↓もしサイトの高さがコンテンツが少ないせいで低くなってもフッターはいっちゃん下にするで
                if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
                    // ↓コンテンツ領域の高さーフッターの高さにposition:fixedをつける
                    // position:fixedは画面の決まった位置に要素を固定させたいときに使う。CSSのやつ
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
        
        <section>
            <!--↓変数sliderのやつ呼び出してこの画像流す-->
            <div class="slider">
                <div class="comment"><img class="slider_img" src="./decoration.img/ldk.png"><p>family</p></div>
                <div class="comment"><img class="slider_img" src="./decoration.img/mountain_house.png"><p>HOUSE</p></div>
                <div class="comment"><img class="slider_img" src="./decoration.img/wood_kitchen.png"><p>kitchen</p></div>
            </div>
        </section>
        
        <article>
            <h2>PICK UP!</h2>
                <ul>
<?php foreach ($err_msg as $value) { //エラーメッセージ表示?>
                    <li><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
<?php foreach ($scc_msg as $value) { //成功ーメッセージ表示?>
                    <li class="blue"><img src="./decoration.img/15cat.png"><?php print $value; ?><img src="./decoration.img/15cat.png"></li>
<?php } ?>
                </ul>
                
            <div class="flex_container">
<?php foreach ($rows as $value) { //ここからforeach?>
                <div class="container">
                    <div><img class="img_size" src="<?php print htmlspecialchars($img_dir . $value['img'] , ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <div><?php print htmlspecialchars($value['name'] , ENT_QUOTES, 'UTF-8'); ?></div>
                    <div>￥<?php print htmlspecialchars($value['price'] , ENT_QUOTES, 'UTF-8'); ?></div>
    <?php if ($value['place'] === 0) { ?>
                    <div class="blue">kitchen</div>
    <?php } ?>
    <?php if ($value['place'] === 1) { ?>
                    <div class="blue">bathroom&restroom</div>
    <?php } ?>
    <?php if ($value['place'] === 2) { ?>
                    <div class="blue">bedroom</div>
    <?php } ?>
    <?php if ($value['place'] === 3) { ?>
                    <div class="blue">outdoor</div>
    <?php } ?>
    <?php if ($value['place'] === 4) { ?>
                    <div class="blue">others</div>
    <?php } ?>
    <?php if ($value['stock'] > 0) {//ストックがあったらカートボタン ?>
                    <form method="post">
                        <input type="submit" value="カートに追加">
                        <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="sql_kind" value="insert_cart">
                    </form>
    <?php } else { //ストックなかったら売り切れ表示?>
                    <div class="glay">sold out</div>
    <?php } ?>
                </div>
<?php } //ここまでforeach?>
            </div>
        </article>
            
        <div><a href="tamazon_cart.php"><input type="submit" value="買い物かごへGO"></a></div>
        
        <div><a href="tamazon_search.php">SEARCH!(click here!)</a></div>
        
        <!--↓上で作ったfooterの関数はここで実行-->
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>