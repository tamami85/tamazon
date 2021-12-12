<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';
require_once './model/ec_item_stock_model.php';
require_once './model/ec_item_master_model.php';
require_once './model/ec_cart_model.php';


$img_dir            = '../item_img/';
$date               = date('Y-m-d H:i:s');
$item_details       = array();
$product_kind_data  = array();
$user_name          = '';
$ec_password        = '';
$sql_kind           = '';
$item_id            = '';
$user_id            = '';
$product_kind       = '';


session_start();
$user_id = get_session_data();


try {//データベース接続
    $dbh = get_db_connect();
    $user_name = match_user_id($dbh, $user_id);

    //ここからはカートに入れるボタン押したらの処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $sql_kind = get_post_data('sql_kind');
        
        if ($sql_kind === 'insert_cart') {//$sql_kindがinsert_cartやったら
        
            $item_id = get_post_data('item_id');
            check_id($item_id);

            if(has_error() === false) {//エラーなかったらカートに商品がある？
                add_cart($dbh, $user_id, $item_id, $date);
            }//エラーなかったらカートに商品がある？
        }//$sql_kindがinsert_cartやった
    }
    
    //検索結果表示
    $product_kind = get_get_data('product_kind');
    check_product_kind($product_kind);

    if (has_error() === false) {//エラーなかったら
        $get_product_kind_data = get_product_kind($dbh, $product_kind);
    }//エラーなかったら
    
    //ここからは商品ひっぱってくるやつ
    if (isset($_GET['search']) === true) {//サーチボタン押されてたら
        $item_details = $get_product_kind_data;
    } else {
        $item_details = get_item_data($dbh);
    }
    

} catch (PDOException $e) {
    set_err_msg('接続できませんでした。理由：'.$e->getMessage());
}//データベース接続


include_once './view/view_home.php';