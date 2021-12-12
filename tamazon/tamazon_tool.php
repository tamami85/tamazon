<?php
$img_dir            = './item_img/';//アップロードしたファイルの保存先
$data               = array();//データの配列を空で用意
$err_msg            = array();//エラーメッセージの配列も空で用意
$scc_msg            = array();//成功メッセージ
$pattern            = '/^[1-9][0-9]*$/';
$pattern_2          = '/^[0-9]*$/';
$pattern_3          = '/^[01]$/';
$pattern_4          = '/^[0-4]*$/';
$sql_kind           = '';
$new_name           = '';
$new_img            = '';//これからアップロードする新しい画像ファイルがここに入るから、空にして準備しとく
$new_price          = '';
$new_stock          = '';
$new_place          = '';
$new_status         = '';
$update_stock       = '';
$change_status      = '';
$item_id            = '';
$date               = date('Y-m-d H:i:s');

$host               = 'localhost';//たまのパソコン
$username           = 'codecamp45262';
$password           = 'codecamp45262';
$dbname             = 'codecamp45262';
$charset            = 'utf8';

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try { // tryとりまデータベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//REQUEST_METHODがポストやったら
        if (isset($_POST['sql_kind']) === TRUE) {//sql_kindは存在してんのか！？＝＝＝してる！
            $sql_kind = $_POST['sql_kind'];//変数にしてあげる
            
            if ($sql_kind === 'insert') {//insertボタン
            
                if (isset($_POST['new_name']) === TRUE) {//相変わらず存在確認
                    $new_name = $_POST['new_name'];
                    if ($new_name === '') {
                        $err_msg[] = '商品名がないです';
                    }
                }
                
                if (isset($_POST['new_price']) === TRUE) {
                    $new_price = $_POST['new_price'];
                    if ($new_price === '') {
                        $err_msg[] = '価格書いてへんやん';
                    } else if (preg_match($pattern_2, $new_price) !== 1) {//整数かどうかチェック
                        $err_msg[] = '待ってこれいくらなん？ちゃんと数字入れてや';
                    }
                }

                if (isset($_POST['new_stock']) === TRUE) {
                    $new_stock = $_POST['new_stock'];
                    if ($new_stock === '') {
                        $err_msg[] = '在庫数が入力されてへんで';
                    } else if (preg_match($pattern_2, $new_stock) !== 1) {//整数かどうかチェック
                        $err_msg[] = '待ってこれ在庫なんぼなん？ちゃんと数字入れてや';
                    }
                }
                
                if (isset($_POST['new_status']) === TRUE) {//ステータスどうするか
                    $new_status = $_POST['new_status'];
//var_dump($new_status);
                    if ($new_status === '') {
                        $err_msg[] = 'ステータスが入力されてへんで';
                    } else if (preg_match($pattern_3, $new_status) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ステータスは1か0しか無理やねん';
                    }
                }
                
                if (isset($_POST['new_place']) === TRUE) {
                    $new_place = $_POST['new_place'];
                    if ($new_place === '') {
                        $err_msg[] = 'どこで使うんか入力されてへんで';
                    } else if (preg_match($pattern_4, $new_place) !== 1) {//整数かどうかチェック
                        $err_msg[] = '待ってこれどこで使うん？ちゃんと数字入れてや';
                    }
                }
                
                if (count($err_msg) === 0) {//上記エラーがなかったら画像を入れるぞう
                    //is_uploaded_fileはPOSTでアップロードされたファイルかどうかを調べる関数
                    //$_FILES['inputで指定したname']['tmp_name']：一時保存ファイル名
                    if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE) {//画像処理1
                        //↓画像の拡張子ゲットだぜ
                        //PATHINFO_EXTENSION　はファイルパスに関する情報を　EXTENSION　の要素全てで返す
                        //$_FILES['inputで指定したname']['name']：ファイル名
                        $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
                        // ↓$extensionが'jpg'か'png'やったら
                        if ($extension === mb_strtolower('jpeg') || $extension === mb_strtolower('png')) {//画像処理2
                            //↓唯一無二のidを作る
                            $new_img = sha1(uniqid(mt_rand(), true)). '.' . $extension;
                            //↓おんなじ名前のファイルがあるかどうかを確認
                            //is_fileは通常のファイルかどうかを調べる関数
                            if (is_file($img_dir . $new_img) !== TRUE) {//画像処理3
                                //↓アップロードされたファイルは指定されたとこに移動して保存されるけど
                                if(move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img) !== TRUE) {//画像処理4
                                    //↓無理やったらエラー出すで
                                    $err_msg[] = 'ファイルアップロードに失敗しました';
                                }//画像処理4終わり
                            } else {
                                $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
                            }//画像処理3終わり
                        } else {
                            $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEG、PNGのみ利用可能です。';
                        }//画像処理2終わり
                    } else {
                        $err_msg[] = 'ファイルを選択してください';
                    }//画像処理1終わり
                }//上記エラーがなかったら画像を入れるぞう終わり
                
                //トランザクション開始
                $dbh->beginTransaction();
                
                if (count($err_msg) === 0 ) { // ほんでもしエラーがなければ、入力した値を入れていく
                    try {// tryこっから下はマスターに値を入れる
                        $sql = 'INSERT INTO ec_item_master
                                    (name,
                                    price,
                                    img,
                                    status,
                                    place,
                                    create_datetime)
                                VALUES
                                    (?, ?, ?, ?, ?, ?)';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $new_name,       PDO::PARAM_STR);
                        $stmt->bindValue(2, $new_price,      PDO::PARAM_INT);
                        $stmt->bindValue(3, $new_img,        PDO::PARAM_STR);
                        $stmt->bindValue(4, $new_status,     PDO::PARAM_INT);
                        $stmt->bindValue(5, $new_place,      PDO::PARAM_INT);
                        $stmt->bindValue(6, $date,           PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $item_id = $dbh->lastInsertId();                                                                                                                                                                                                                                                                                                   // $item_id = $dbh->lastInsertId();
                        //こっから下はストックに値を入れる
                        $sql = 'INSERT INTO ec_item_stock
                                    (item_id, stock, create_datetime) 
                                VALUES
                                    (?, ?, ?)';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $item_id,       PDO::PARAM_INT);
                        $stmt->bindValue(2, $new_stock,     PDO::PARAM_INT);
                        $stmt->bindValue(3, $date,          PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $dbh->commit();
                        $scc_msg[] = 'データが登録できました';
                    } catch (PDOException $e) {
                        $dbh->rollback();//はじめに戻って何もなかったことにしたるねん。これがないと辻褄が合わん。
                        throw $e;//例外投げ！！！！
                    }//tryこっから下はドリンクマスターに値を入れる終わり
                }// ほんでもしエラーがなければ、入力した値を入れていく終わり
            }//insertボタンおわり
        
            if ($sql_kind === 'update') {//updateボタン
        
                if (isset($_POST['update_stock']) === TRUE) {//相変わらず存在確認
                    $update_stock = $_POST['update_stock'];
                    if ($update_stock === '') {
                        $err_msg[] = '在庫数がないです';
                    } else if (preg_match($pattern_2, $update_stock) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }
                if (isset($_POST['item_id']) === TRUE) {//相変わらず存在確認
                    $item_id = $_POST['item_id'];
                    if ($item_id === '') {
                        $err_msg[] = '商品が正しくありません';
                    } else if (preg_match($pattern, $item_id) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }
                if (count($err_msg) === 0) {//エラーがなかったらアプデ
                    try {
                        $sql = 'UPDATE ec_item_stock
                                SET stock = ?,
                                    update_datetime = ?
                                WHERE item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $update_stock,      PDO::PARAM_INT);
                        $stmt->bindValue(2, $date,              PDO::PARAM_STR);
                        $stmt->bindValue(3, $item_id,           PDO::PARAM_INT);
                        $stmt->execute();
                        //成功メッセージを用意
                        $scc_msg[] = '在庫更新完了イェア';
                    } catch (PDOException $e) {
                        $err_msg[] = '更新できませんでした';
                        throw $e;
                    }
                }//エラーがなかったらアプデ終わり
            }//updateボタン終わり
            
            if ($sql_kind === 'change') {//changeボタン
                if (isset($_POST['change_status']) === TRUE) {//相変わらず存在確認change_status
                    $change_status = $_POST['change_status'];
//var_dump($change_status);
                    if ($change_status === '') {
                        $err_msg[] = 'ちゃんとステータス入力してや';
                    } else if (preg_match($pattern_3, $change_status) !== 1) {//ステータスチェック
                        $err_msg[] = 'ステータスは1か0しかむりやで';
                    }
                }//相変わらず存在確認change_status終わり
                if (isset($_POST['item_id']) === TRUE) {//相変わらず存在確認item_id
                    $item_id = $_POST['item_id'];
                    if ($item_id === '') {
                        $err_msg[] = '商品が正しくありません';
                    } else if (preg_match($pattern, $item_id) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }//相変わらず存在確認item_id終わり
                if (count($err_msg) === 0) {//エラーがなかったらステータス変える
                    try {
                        $sql = 'UPDATE ec_item_master
                                SET status = ?,
                                    update_datetime = ?
                                WHERE item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $change_status,     PDO::PARAM_INT);
                        $stmt->bindValue(2, $date,              PDO::PARAM_STR);
                        $stmt->bindValue(3, $item_id,          PDO::PARAM_INT);
                        $stmt->execute();
                        //成功メッセージを用意
                        $scc_msg[] = 'ステータス更新完了イェア';
                    } catch (PDOException $e) {
                        $err_msg[] = '更新できませんでした';
                        throw $e;
                    }
                }//エラーがなかったらステータス変える終わり
            }//changeボタン終わり
            
            if ($sql_kind === 'delete') {//deleteボタン
                if (isset($_POST['delete_item']) === TRUE) {//エラーがなかったらデータ消す
                    $delete_item = $_POST['delete_item'];
//var_dump($delete_item);
                }
                if (isset($_POST['item_id']) === TRUE) {//相変わらず存在確認item_id
                    $item_id = $_POST['item_id'];
                    if ($item_id === '') {
                        $err_msg[] = '商品が正しくありません';
                    } else if (preg_match($pattern, $item_id) !== 1) {//整数かどうかチェック
                        $err_msg[] = 'ちゃんと数字入れてや';
                    }
                }//相変わらず存在確認item_id終わり
                $dbh->beginTransaction();
                if (count($err_msg) === 0) {//エラーがなかったらデータ消す終わり
                    try {
                        $sql = 'DELETE FROM ec_item_master
                                WHERE item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $item_id,          PDO::PARAM_INT);
                        $stmt->execute();

                        $sql = 'DELETE FROM ec_item_stock
                                WHERE item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $item_id,          PDO::PARAM_INT);
                        $stmt->execute();
                        //print $item_id;
                        $dbh->commit();
                        //成功メッセージを用意
                        $scc_msg[] = 'データ消しといたイェア';
                    } catch (PDOException $e) {
                        $dbh->rollback();
                        throw $e;
                    }
                }//エラーがなかったらデータ消す終わり
            }
        }//sql_kindは存在してんのか！？＝＝＝してる！終わり
    }//REQUEST_METHODがポストやったら終わり
    
    //↓ハイ次は既存のアップロードされた画像ファイル名とかの取得
    try { // tryテーブル組み合わせるSQL文
      // ↓SQL文を作成からの準備からの実行からの配列でデータ取得
        $sql = 'SELECT 
                    ec_item_master.item_id,
                    ec_item_master.img,
                    ec_item_master.name,
                    ec_item_master.price,
                    ec_item_master.place,
                    ec_item_master.status,
                    ec_item_stock.stock
                FROM ec_item_master
                LEFT OUTER JOIN ec_item_stock
                ON ec_item_master.item_id = ec_item_stock.item_id';
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);//取得したデータは相変わらず配列やからばらけさして、１行ずつ取得
        //print_r($rows);
        
    } catch (PDOException $e) {
        $err_msg[] =  'データベース処理でエラーが発生しました。理由：'.$e->getMessage();
    }// tryテーブル組み合わせるSQL文終わり

} catch (PDOException $e) {
    // 接続失敗した場合
    $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    throw $e;
}// tryとりまデータベースに接続終わり

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>tamazon</title>
        <style>
            section {
                margin-bottom: 20px;
                border-top: solid 1px;
            }
    
            table {
                width: 900px;
                border-collapse: collapse;
            }
    
            table, tr, th, td {
                border: solid 1px;
                padding: 10px;
                text-align: center;
            }
    
            caption {
                text-align: left;
            }
    
            .text_align_right {
                text-align: right;
            }
            
            .img_size {
                width: 150px;
                height: 100px;
            }
    
            .item_name_width {
                width: 100px;
            }
    
            .input_text_width {
                width: 60px;
            }
    
            .status_false {
                background-color: #A9A9A9;
            }
        </style>
    </head>
    <body>
        <h1>tamazon管理ツール</h1>
            <a href="./tamazon_user_table.php" target="_blank">tamazonユーザー管理ページへGO</a>
            <ul>
<?php foreach ($err_msg as $value) { ?>
                <li><?php print $value; ?></li>
<?php } ?>
<?php foreach ($scc_msg as $value) { ?>
                <li><?php print $value; ?></li>
<?php } ?>
            </ul>
        <section>
            <h2>新規商品追加</h2>
            <form method="post" enctype="multipart/form-data">
                <div><label>名前: <input type="text" name="new_name" value=""></label></div>
                <div><label>値段: <input type="text" name="new_price" value=""></label></div>
                <div><label>個数: <input type="text" name="new_stock" value=""></label></div>
                <div>
                    <select name="new_status">
                        <option value="0">非公開</option>
                        <option value="1">公開</option>
                    </select>
                </div>
                <div>
                    <select name="new_place">
                        <option value="0">キッチン</option>
                        <option value="1">バス・トイレ</option>
                        <option value="2">寝室</option>
                        <option value="3">外</option>
                        <option value="4">その他</option>
                    </select>
                </div>
                <div><input type="file" name="new_img"></div>
                <input type="hidden" name="sql_kind" value="insert">
                <div><input type="submit" value="■□■□■商品追加■□■□■"></div>
            </form>
        </section>
        <section>
            <h2>商品情報変更</h2>
            <table>
                <caption>商品一覧</caption>
                <tr>
                    <th>商品画像</th>
                    <th>商品名</th>
                    <th>価格</th>
                    <th>在庫数</th>
                    <th>ここで便利</th>
                    <th>ステータス</th>
                    <th>操作</th>
                </tr>
<?php foreach ($rows as $value) { ?>
        <?php if ($value['status'] === 1) { ?>
                <tr>
        <?php } else { ?>
                <tr class="status_false">
        <?php } ?>
                    <td><img src="<?php print htmlspecialchars($img_dir . $value['img'] , ENT_QUOTES, 'UTF-8'); ?>" class="img_size"></td>
                    <td class="item_name_width"><?php print htmlspecialchars($value['name'] , ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="text_align_right"><?php print htmlspecialchars($value['price'] , ENT_QUOTES, 'UTF-8'); ?>円</td>
                    <!--↓update_stockをhiddenでわける-->
                    <td>
                        <form method="post">
                            <input type="text"  class="input_text_width text_align_right" name="update_stock" value="<?php print htmlspecialchars($value['stock'] , ENT_QUOTES, 'UTF-8'); ?>">個&nbsp;&nbsp;<input type="submit" value="変更">
                            <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'] , ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="sql_kind" value="update">
                        </form>
                    </td>
        <?php if ($value['place'] === 0) { ?>
                    <td>キッチン</td>
        <?php } ?>
        <?php if ($value['place'] === 1) { ?>
                    <td>バス・トイレ</td>
        <?php } ?>
        <?php if ($value['place'] === 2) { ?>
                    <td>寝室</td>
        <?php } ?>
        <?php if ($value['place'] === 3) { ?>
                    <td>外</td>
        <?php } ?>
        <?php if ($value['place'] === 4) { ?>
                    <td>その他</td>
        <?php } ?>
                    <td>
                        <form method="post">
        <?php if ($value['status'] === 1) { ?>
                            <input type="submit" value="公開 → 非公開">
                            <input type="hidden" name="change_status" value="0">
        <?php } else { ?>
                            <input type="submit" value="非公開 → 公開">
                            <input type="hidden" name="change_status" value="1">
        <?php } ?>
                            <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'] , ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="sql_kind" value="change">
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="submit" name="delete_item" value="削除">
                            <input type="hidden" name="item_id" value="<?php print htmlspecialchars($value['item_id'] , ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="sql_kind" value="delete">
                        </form>
                    </td>
                </tr>
<?php } ?>
            </table>
        </section>
    </body>
</html>