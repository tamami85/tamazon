<?php

/*
ステータスが1以外のアイテムデータを取得する
*/
function get_item_data($dbh) {
    $sql = 'SELECT
                ec_item_master.item_id,
                ec_item_master.img,
                ec_item_master.name,
                ec_item_master.price,
                ec_item_master.product_kind,
                ec_item_stock.stock
            FROM
                ec_item_master
            LEFT OUTER JOIN
                ec_item_stock
            ON
                ec_item_master.item_id = ec_item_stock.item_id
            WHERE
                ec_item_master.status = 1';//公開の商品だけ取得
                
    return get_as_array($dbh, $sql);
}

/*
指定された種類の商品データを取得する
*/
function get_product_kind($dbh, $product_kind) {
    $sql = 'SELECT
                ec_item_master.item_id,
                ec_item_master.img,
                ec_item_master.name,
                ec_item_master.price,
                ec_item_master.product_kind,
                ec_item_stock.stock
            FROM
                ec_item_master
            LEFT OUTER JOIN
                ec_item_stock
            ON
                ec_item_master.item_id = ec_item_stock.item_id
            WHERE
                ec_item_master.product_kind = ?
            AND
                ec_item_master.status = 1';//使う商品だけ取得
                    
    $data = array($product_kind);
    return get_as_array($dbh, $sql, $data);
}

/*
新しく追加したやつをデータベースに登録する
*/
function insert_item_master($dbh, $new_name, $new_price, $new_img, $new_status, $product_kind, $date) {
    $sql = 'INSERT INTO
                ec_item_master
                (name,
                price,
                img,
                status,
                product_kind,
                create_datetime)
            VALUES
                (?, ?, ?, ?, ?, ?)';
                
    $data = array($new_name, $new_price, $new_img, $new_status, $product_kind, $date);
    return get_as_array($dbh, $sql, $data);
}
/*
新しく追加したやつをec_stockに登録する
*/
function insert_item_stock($dbh, $item_id, $new_stock, $date) {
    $sql = 'INSERT INTO
                ec_item_stock
                (item_id, stock, create_datetime) 
            VALUES
                (?, ?, ?)';
                
    $data = array($item_id, $new_stock, $date);
    return execute_query($dbh, $sql, $data);
}

/*
ステータスを更新する
*/
function update_item_status($dbh, $change_status, $date, $item_id) {
    $sql = 'UPDATE
                ec_item_master
            SET
                status = ?,
                update_datetime = ?
            WHERE
                item_id = ?';
            
    $data = array($change_status, $date, $item_id);
    return execute_query($dbh, $sql, $data);
}
/*
ボタン押したらec_item_masterから情報を消す
*/
function delete_item_master($dbh, $item_id) {
    $sql = 'DELETE FROM
                ec_item_master
            WHERE
                item_id = ?';
                
    $data = array($item_id);
    return execute_query($dbh, $sql, $data);
}
/*
ボタン押したらec_item_stockから情報を消す
*/
function delete_item_stock($dbh, $item_id) {
    $sql = 'DELETE FROM
                ec_item_stock
            WHERE
                item_id = ?';
            
    $data = array($item_id);
    return execute_query($dbh, $sql, $data);
}

/*
アイテム情報を表示
*/
function select_item_data($dbh) {
    $sql = 'SELECT 
                ec_item_master.item_id,
                ec_item_master.img,
                ec_item_master.name,
                ec_item_master.price,
                ec_item_master.product_kind,
                ec_item_master.status,
                ec_item_stock.stock
            FROM
                ec_item_master
            LEFT OUTER JOIN
                ec_item_stock
            ON
                ec_item_master.item_id = ec_item_stock.item_id';
                
    return get_as_array($dbh, $sql);
}
