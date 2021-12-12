<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';
require_once './model/ec_item_stock_model.php';
require_once './model/ec_item_master_model.php';
require_once './model/ec_cart_model.php';



$img_dir            = '../item_img/';//アップロードしたファイルの保存先
$data               = array();//データの配列を空で用意
$date               = date('Y-m-d H:i:s');
$sql_kind           = '';
$new_name           = '';
$new_img            = '';//これからアップロードする新しい画像ファイルがここに入るから、空にして準備しとく
$new_price          = '';
$new_stock          = '';
$product_kind          = '';
$new_status         = '';
$update_stock       = '';
$change_status      = '';
$item_id            = '';

try { // tryとりまデータベースに接続
    $dbh = get_db_connect();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//REQUEST_METHODがポストやったら
        $sql_kind = get_post_data('sql_kind');
        
        if ($sql_kind === 'insert') {//insertボタン
        
            $new_name       = get_post_data('new_name');
            $new_price      = get_post_data('new_price');
            $new_stock      = get_post_data('new_stock');
            $new_status     = get_post_data('new_status');
            $product_kind   = get_post_data('product_kind');
            $name           = $new_name;
            $stock_number   = $new_stock;
            $status_number  = $new_status;
        
            check_name($new_name);
            check_price($new_price);
            check_stock($stock_number);
            check_status($status_number);
            check_product_kind($product_kind);

            if (has_error() === false) {//上記エラーがなかったら画像を入れるぞう
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
                                set_err_msg('ファイルアップロードに失敗しました');
                            }//画像処理4終わり
                        } else {
                            set_err_msg('ファイルアップロードに失敗しました。再度お試しください。');
                        }//画像処理3終わり
                    } else {
                        set_err_msg('ファイル形式が異なります。画像ファイルはJPEG、PNGのみ利用可能です。');
                    }//画像処理2終わり
                } else {
                    set_err_msg('ファイルを選択してください');
                }//画像処理1終わり
            }//上記エラーがなかったら画像を入れるぞう終わり
            
            $dbh->beginTransaction();
            if (has_error() === false) { // ほんでもしエラーがなければ、入力した値を入れていく
                insert_item_master($dbh, $new_name, $new_price, $new_img, $new_status, $product_kind, $date);
                $item_id = $dbh->lastInsertId();
                insert_item_stock($dbh, $item_id, $new_stock, $date);
                
                $dbh->commit();
                set_scc_msg('データが登録できました');
            } else {
                $dbh->rollback();//はじめに戻って何もなかったことにしたるねん。これがないと辻褄が合わん。
                set_err_msg('データが登録できませんでした');
            }// ほんでもしエラーがなければ、入力した値を入れていく終わり
        }//insertボタンおわり
        
        if ($sql_kind === 'update') {//updateボタン
            
            $update_stock   = get_post_data('update_stock');
            $item_id        = get_post_data('item_id');//user_commentがポストされたらget_post_data関数発動
            $stock_number   = $update_stock;
            
            check_stock($stock_number);
            check_id($item_id);

            if (has_error() === false) {//エラーがなかったら在庫数アプデ
                update_item_stock($dbh, $update_stock, $date, $item_id);
                set_scc_msg('在庫数を変更しました');
            } else {
                set_err_msg('在庫数を変更できませんでした');
            }//エラーがなかったら在庫数アプデ終わり
        }//updateボタン終わり
        
        if ($sql_kind === 'change') {//changeボタン
        
            $change_status  = get_post_data('change_status');
            $item_id        = get_post_data('item_id');
            $status_number  = $change_status;
            
            check_status($status_number);
            check_id($item_id);
            
            if (has_error() === false) {//エラーがなかったらステータス変える
                update_item_status($dbh, $change_status, $date, $item_id);
                set_scc_msg('ステータスを更新しました');
            } else {
                set_err_msg('ステータスを更新できませんでした');
            }//エラーがなかったらステータス変える終わり
        }//changeボタン終わり

        if ($sql_kind === 'delete') {//deleteボタン
        
            $delete_item    = get_post_data('delete_item');
            $item_id        = get_post_data('item_id');
            
            check_id($item_id);
            
            $dbh->beginTransaction();
            if (has_error() === false) {//エラーがなかったらデータ消す終わり
                delete_item_master($dbh, $item_id);
                delete_item_stock($dbh, $item_id);
                $dbh->commit();
                set_scc_msg('データを削除しました');
            } else {
                $dbh->rollback();
                set_err_msg('データの削除ができませんでした');
            }//エラーがなかったらデータ消す終わり
        }//deleteボタン
    }//REQUEST_METHODがポストやったら終わり
    
    $item_data = select_item_data($dbh);//アイテム表示

} catch (PDOException $e) {
    // 接続失敗した場合
    set_err_msg('DBエラー：'.$e->getMessage());
    throw $e;
}// tryとりまデータベースに接続終わり

include_once './view/view_tool.php';