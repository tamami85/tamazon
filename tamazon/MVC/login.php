<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';


$user_name      = '';
$ec_password    = '';
$user_id        = '';

try {
    $dbh = get_db_connect();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {//ポストされたら
    
        $user_name      = get_post_data('user_name');
        $ec_password    = get_post_data('password');
        check_user_name($user_name);
        check_password($ec_password);

        if (has_error() === false) {//エラーなかったら
        
            $user_data = get_user_data($dbh, $user_name);

            if (count($user_data) === 0) {
                set_err_msg('ユーザー名が違います');
            }
            $user_id = $user_data['user_id'];
            
            //　↓ハッシュドポテトしたパスワードを入力したパスワードと比べる
            if (password_verify($ec_password, $user_data['password'])) {
                session_start();
                //ほんでTRUEやったらセッションに入れたげる
                $_SESSION['login_id'] = $user_id;
                header('Location: home.php');//tamazon_homeに行く

            } else {
                set_err_msg('パスワードが違います');
            }
        }//エラーなかったら
    }//ポストされたら

} catch (PDOException $e) {
    set_err_msg('接続できませんでした。理由：'.$e->getMessage());
}//データベース接続


include_once './view/view_login.php';