<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';
require_once './model/ec_item_stock_model.php';
require_once './model/ec_item_master_model.php';
require_once './model/ec_cart_model.php';


$img_dir        = '../item_img/';
$date           = date('Y-m-d H:i:s');
$user_id        = '';
$item_id        = '';
$cal_result     = '';
$amount         = '';



//ここでログインしてるか確認
session_start();
$user_id = get_session_data();

try {//データベース接続
    $dbh        = get_db_connect();
    $user_name  = match_user_id($dbh, $user_id);

    //amount変更する
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){//ポストされたら
        $sql_kind = get_post_data('sql_kind');

        //買いたい個数変更
        if ($sql_kind === 'change_amount'){//change_amountボタン押したら
            
            $amount     = get_post_data('amount');
            $item_id    = get_post_data('item_id');
            check_amount($amount);
            check_id($item_id);
            
            if (has_error() === false){//エラーがなかったらamountアプデ
                update_cart_amount($dbh, $amount, $date, $item_id);
                set_scc_msg('購入数を変更しました');
            }//エラーがなかったらamountアプデ終わり
        }//change_amountボタン押したら
        
        //買いたいもの消す    
        if ($sql_kind === 'delete'){//deleteボタン
        
            $delete_item    = get_post_data('delete_item');
            $item_id        = get_post_data('item_id');
            check_id($item_id);
            
            if (has_error() === false){//エラーがなかったら消す
                delete_cart_item($dbh, $item_id);
                set_scc_msg('カート内商品を削除しました');
            }//エラーがなかったら消す
        }//deleteボタン
    }//ポストされたら

    
    //データベースからec_cart持ってくる
    $cart_data  = get_cart_data($dbh, $user_id);
    $cal_result = cal_result($cart_data);//計算結果

    
} catch (PDOException $e){
    echo '接続できませんでした。理由：'.$e->getMessage();
}


include_once './view/view_cart.php';