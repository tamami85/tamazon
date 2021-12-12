<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';


$user_name_data = array();
$date           = date('Y-m-d H:i:s');
$user_name      = '';
$ec_password    = '';

try {//データベース接続
    $dbh = get_db_connect();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){//ポストされたら
    
        $user_name      = get_post_data('user_name');
        $ec_password    = get_post_data('password');
        check_user_name($user_name);
        check_password($ec_password);
        
        $user_name_data = get_user_name($dbh);
        is_user_name_unique($user_name_data, $user_name);

        if (has_error() === false) {
            // ↓もしエラーなかったらパスワードをハッシュ化してユーザーテーブルにインサート
            $ec_password = password_hash($ec_password, PASSWORD_DEFAULT);//ハッシュドポテトして入れる
            insert_user_data($dbh, $user_name, $ec_password, $date);

            set_scc_msg('アカウントを作成しました');
        } else {
            set_err_msg('アカウントを作成できませんでした');
        }//エラーなかったら
        
    }//ポストされたら
} catch (PDOException $e) {
    set_err_msg('接続できませんでした。理由：'.$e->getMessage());
}//データベース接続

include_once './view/view_register.php';