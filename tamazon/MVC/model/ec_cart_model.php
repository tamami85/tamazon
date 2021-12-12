<?php

/*
カート内のデータを持ってくる
*/
function get_cart_data($dbh, $item_id) {
    $sql = 'SELECT
                ec_cart.item_id,
                ec_cart.amount,
                ec_item_master.img,
                ec_item_master.name,
                ec_item_master.price,
                ec_item_master.status,
                ec_item_stock.stock
            FROM
                ec_cart
            LEFT OUTER JOIN
                ec_item_master
            ON
                ec_cart.item_id = ec_item_master.item_id
            LEFT OUTER JOIN
                ec_item_stock
            ON
                ec_item_master.item_id = ec_item_stock.item_id
            WHERE
                ec_cart.user_id = ?';
                
    $data = array($item_id);
    return get_as_array($dbh, $sql, $data);
}

/*
カート内商品の計算結果
*/
function cal_result_msg($user_id, $cart_data) {
    if (count($cart_data) === 0) {
        set_err_msg('カートが空です');
    } else {
        foreach($cart_data as $value) {
            if ($value['amount'] > $value['stock']) {
                set_err_msg($value['name'] . 'は在庫が少ないため購入できません');
            } else if ($value['status'] !== 1) {
                set_err_msg($value['name'] . 'は販売することはできません');
            }
        }
    }
}

function cal_result($cart_data) {
    $cal_result = 0;
    foreach($cart_data as $value) {
        $cal_result = $cal_result + $value['price'] * $value['amount'];//合計出す
    }
    return $cal_result;
}

/*
最終的に買った商品をストックから減らす
カートのデータを削除する
これをトランザクションで実行する
*/
function purchase_result($dbh, $date, $user_id, $cart_data) {
    $dbh->beginTransaction();
    try {
        foreach($cart_data as $value) {
            $amount     = $value['amount'];
            $item_id    = $value['item_id'];
            update_stock_result($dbh, $amount, $date, $item_id);
        }
        delete_cart_data($dbh, $user_id);
        $dbh->commit();
        set_scc_msg('お買い上げありがとうございます');
    } catch (PDOException $e) {
        set_err_msg('購入できませんでした');
        $dbh->rollback();
    }
}


/*
カートに同一商品があるか確認する
データベースからカート情報のamountを取ってくる
*/
function get_cart_amount($dbh, $user_id, $item_id) {
    $sql = 'SELECT
                amount
            FROM
                ec_cart
            WHERE
                user_id = ?
            AND
                item_id = ?';
                
    $data = array($user_id, $item_id);
    return get_as_array($dbh, $sql, $data);
}

/*
もし1つでもあれば、amountに1を足す
*/
function amount_plus_one($dbh, $user_id, $item_id) {
    $sql = 'UPDATE
                ec_cart
            SET
                amount = amount + 1
            WHERE
                user_id = ?
            AND
                item_id = ?';
            
    $data = array($user_id, $item_id);
    return execute_query($dbh, $sql, $data);
}
/*
一つもなければ、ユーザー名とかの情報もすべてカート内にインサートする
*/
function insert_cart_data_all($dbh, $user_id, $item_id, $date) {
    $sql = 'INSERT INTO 
                ec_cart
                (user_id,
                item_id,
                amount,
                create_datetime)
            VALUES
                (?, ?, ?, ?)';
                
    $data = array($user_id, $item_id, 1, $date);
    return execute_query($dbh, $sql, $data);
}

/*
カートに商品を追加する
もしすでにカート内に同一商品があれば、1増やし
なければデータをせんぶ入れる
それを一連の流れにする
*/
function add_cart($dbh, $user_id, $item_id, $date) {
    
    $cart_amount = get_cart_amount($dbh, $user_id, $item_id);
    if (count($cart_amount) > 0) {
        amount_plus_one($dbh, $user_id, $item_id);//もうすでにカートに同じ商品のデータがあったら1を足す
        set_scc_msg('商品を追加しました');
    } else {
        insert_cart_data_all($dbh, $user_id, $item_id, $date);//カートに同じ商品のデータがなかったら全部入れる
        set_scc_msg('商品を追加しました');
    }
}
/*
カート内のamountをアップデートする
*/
function update_cart_amount($dbh, $amount, $date, $item_id) {
    $sql = 'UPDATE
                ec_cart
            SET
                amount = ?,
                update_datetime = ?
            WHERE
                item_id = ?';
                
    $data = array($amount, $date, $item_id);
    return execute_query($dbh, $sql, $data);
}

/*
カート内のアイテム情報を削除する
*/
function delete_cart_item($dbh, $item_id) {
    $sql = 'DELETE FROM
                ec_cart
            WHERE
                item_id = ?';
        
    $data = array($item_id);
    return execute_query($dbh, $sql, $data);
}

/*
カートのデータを削除する
*/
function delete_cart_data($dbh, $user_id) {
    $sql = 'DELETE FROM
                ec_cart
            WHERE
                user_id = ?';
                
    $data = array($user_id);
    return execute_query($dbh, $sql, $data);
}


