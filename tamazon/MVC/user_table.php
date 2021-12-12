<?php
require_once './conf/const.php';
require_once './model/common.php';

require_once './model/ec_user_model.php';



$err_msg = array();

try {
    $dbh = get_db_connect();
    
    $user_data = select_user_data($dbh);
    
} catch (PDOException $e) {
    echo '接続できませんでした。理由：'.$e->getMessage();
}


include_once './view/view_user_table.php';