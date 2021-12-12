<?php

/*
ちゃんとログインされてるか確認して、ログインできてなかったらログインページに飛ばす
ログインできてる人なら、$user_nameを返す
*/
function match_user_id($dbh, $user_id) {
    $sql = 'SELECT
                user_name
            FROM
                ec_user
            WHERE
                user_id = ?';
                
    return get_as_one_array($dbh, $sql, [$user_id])['user_name'];
}

/*
ユーザー情報を照会する
*/
function get_user_data($dbh, $user_name) {
    $sql = 'SELECT
                user_id, password
            FROM
                ec_user
            WHERE
                user_name = ?';
            
    $data = array($user_name);
    return get_as_one_array($dbh, $sql, $data);
}

/*
ユーザーテーブルからユーザー情報を持ってくる
*/
function get_user_name($dbh) {
    $sql = 'SELECT
                user_name
            FROM
                ec_user';
                
    return get_as_array($dbh, $sql);
}

/*
エラーがなかったユーザー情報を登録する
*/
function insert_user_data($dbh, $user_name, $ec_password, $date) {
    $sql = 'INSERT INTO
                ec_user
                (user_name, password, create_datetime)
            VALUES
                (?, ?, ?)';
                
    $data = array($user_name, $ec_password, $date);
    return get_as_array($dbh, $sql, $data);
}

/*
ユーザー情報を登録するためのバリデ
*/
function is_user_name_unique($user_name_data, $user_name) {
    foreach ($user_name_data as $value) {
        if ($user_name === $value['user_name']) {//ユーザー名がかぶってへんか確認
            set_err_msg('そのユーザー名は使えません');
        }
    }
}

/*
ユーザーデータテーブルの取得
*/
function select_user_data($dbh) {
    $sql = 'SELECT
                user_name, create_datetime
            FROM
                ec_user';
                
    return get_as_array($dbh, $sql);
}
