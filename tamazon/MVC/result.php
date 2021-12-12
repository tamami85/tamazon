<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';
require_once './model/ec_item_stock_model.php';
require_once './model/ec_item_master_model.php';
require_once './model/ec_cart_model.php';


$img_dir        = '../item_img/';
$date           = date('Y-m-d H:i:s');
$user_name      = '';
$user_id        = '';
$item_id        = '';
$cal_result     = '';
$amount         = '';
$stock          = '';

//ここでログインしてるか確認
session_start();
$user_id = get_session_data();

try {//データベース接続
    $dbh        = get_db_connect();
    $user_name  = match_user_id($dbh, $user_id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//ポストされたら

        $cart_data  = get_cart_data($dbh, $user_id);
        $cal_result = cal_result($cart_data);//計算結果

        cal_result_msg($user_id, $cart_data);//計算する前に在庫とか色々見て買えんものがないかチェック

        if (has_error() === false) {
            purchase_result($dbh, $date, $user_id, $cart_data);
        }

    }//ポストされたら



} catch (PDOException $e) {
    echo '接続できませんでした。理由：'.$e->getMessage();
}

include_once './view/view_result.php';