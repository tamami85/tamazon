<?php

/*
最終的に買った商品をストックから減らす
*/
function update_stock_result($dbh, $amount, $date, $item_id) {
    // try {
    $sql = 'UPDATE
                ec_item_stock
            SET
                stock = stock - ?,
                update_datetime = ?
            WHERE
                item_id = ?';
            
    $data = array($amount, $date, $item_id);
    return execute_query($dbh, $sql, $data);
}

/*
在庫を更新する
*/
function update_item_stock($dbh, $update_stock, $date, $item_id) {
    $sql = 'UPDATE
                ec_item_stock
            SET
                stock = ?,
                update_datetime = ?
            WHERE
                item_id = ?';
                
    $data = array($update_stock, $date, $item_id);
    return execute_query($dbh, $sql, $data);
}
