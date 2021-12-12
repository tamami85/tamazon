<?php
/*
htmlのセキュリティ
*/
function entity_str($str) {//htmlのセキュリティ
    return htmlspecialchars($str, ENT_QUOTES, HTML_CHARACTER_SET);
}
/*

*/
function entity_assoc_array($assoc_array) {
    foreach ($assoc_array as $key => $value) {
        foreach ($value as $keys => $values) {
            $assoc_array[$key][$keys] = entity_str($values);
        }
    }
    return $assoc_array;
}

/*
データベースアクセス
*/
function get_db_connect() {//データベースアクセス
    try {
        $dbh = new PDO(DSN, DB_USER, DB_PASSWD, array(PDO::MYSQL_ATTR_INIT_COMMAND => DB_CHARSET));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        throw $e;
    }
    return $dbh;//アクセスした状態を返す
}


/*
セレクト文のときの関数
*/
function get_as_array($dbh, $sql, $data = array()) {//3つ飛んでこんくてもからの配列入れとく
    try {
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PODException $e) {
        set_err_msg('データ取得に失敗しました');
    }
    return $rows;
}
/*
セレクト文のときの関数(一行だけ取得)
*/
function get_as_one_array($dbh, $sql, $data = array()) {//3つ飛んでこんくてもからの配列入れとく
    try {
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);
        $row = $stmt->fetch();
    } catch (PODException $e) {
        set_err_msg('データ取得に失敗しました');
    }
    return $row;
}
/*
insert,update,deleteのときの関数
*/
function execute_query($dbh, $sql, $data = array()) {
    try{//$dataはクエリを実行して実行結果を配列で取得したいとき、入れてあげるために準備。空っぽ状態で準備してるから、もし取得せんくても空でおいてるだけやから問題なっしぶる
        $stmt = $dbh->prepare($sql);//データベースに$sqlを命令する準備して、$statementっていうあだ名つける
        return $stmt->execute($data);//$sqlの命令を実行する。これが戻り値。その時、プレースホルダーがあるならは$paramsに連想配列でぶちこまれる
    }catch(PDOException $e) {//あら残念エラーやったら
        set_err_msg('更新に失敗しました');//「エラーかましてきたらどうすんの？関数（function.php内）」使って、セッション箱に入れる
    }
}

/*
なにかしらポストされたら、配列に入れる
*/
function get_post_data($key) {
    $str = '';
    if (isset($_POST[$key]) === TRUE) {//もし何かしらポストされたら
        $str = trim($_POST[$key]);//トリミングして$strに入れとく
    }
    return $str;//$strを返す
}

/*
なにかしらゲットされたら、配列に入れる
*/
function get_get_data($key) {
    $str = '';
    if (isset($_GET[$key]) === TRUE) {//もし何かしらゲットされたら
        $str = trim($_GET[$key]);//トリミングして$strに入れとく
    }
    return $str;//$strを返す
}

/*
ログインしてる人かどうか見極める
*/
function get_session_data() {
    if (isset($_SESSION['login_id']) === TRUE) {//もし何かしらsession
        $user_id = trim($_SESSION['login_id']);//トリミングして$strに入れとく
    } else {
        // 非ログインの場合、ログインページへリダイレクト
        header('Location: login.php');
        exit;
    }
    return $user_id;//$user_idを返す
}


/*
item_idのチェック
*/
function check_id($item_id) {
    $pattern = '/^[1-9][0-9]*$/';
    if ($item_id === '') {
        set_err_msg('商品が正しくありません');
    } else if (preg_match($pattern, $item_id) !== 1) {//整数かどうかチェック
        set_err_msg('アイテムを選んでください');
    }
}

/*
名前がなかったときのやつ
*/
function check_name($name) {//お金がポストされたら
    //ここ以下のときエラーメッセージ出すぞ
    if ($name === '') {
        set_err_msg('名前を入力してください');
    }
}

/*
ユーザーネームのチェック
*/
function check_user_name($user_name) {
    $pattern = '/^[0-9a-zA-Z]{6,}$/';
    if ($user_name === '') {
        set_err_msg('ユーザー名を入力してください');
    } else if (preg_match($pattern, $user_name) !== 1) {
        set_err_msg('ユーザー名は6文字以上の半角英数字です');
    }
}

/*
入力されたストックのチェック
*/
function check_stock($stock_number) {
    $pattern = '/^[0-9]*$/';
    if ($stock_number === '') {
        set_err_msg('在庫数が入力されていません');
    } else if (preg_match($pattern, $stock_number) !== 1) {//整数かどうかチェック
        set_err_msg('在庫数を入力してください');
    }
}

/*
新しく入力されたステータストックのチェック
*/
function check_status($status_number) {
    $pattern = '/^[01]$/';
    if ($status_number === '') {
        set_err_msg('ステータスが入力されていません');
    } else if (preg_match($pattern, $status_number) !== 1) {//整数かどうかチェック
        set_err_msg('適切な入力ではありません');
    }
}

/*
パスワードが入力されてるか確認
*/
function check_password($ec_password) {
    $pattern = '/^[0-9a-zA-Z]{6,}$/';
    if ($ec_password === '') {
        set_err_msg('パスワードを入力してください');
    } else if (preg_match($pattern, $ec_password) !== 1) {
        set_err_msg('パスワードは6文字以上の半角英数字です');
    }
}

/*
カート内の個数をチェックする
*/
function check_amount($amount) {
    $pattern = '/^[0-9]*$/';
    if ($amount === '') {
        set_err_msg('必要な数を教えて下さい');
    } else if (preg_match($pattern, $amount) !== 1) {//整数かどうかチェック
        set_err_msg('適切な入力ではありません');
    }
}
/*
どこで使うのかををチェックする
*/
function check_product_kind($product_kind) {
    $pattern = '/^[0-4]*$/';
    if (isset($_GET['search']) === true && $product_kind === '') {
        set_err_msg('どこで使うのかを教えて下さい');
    } else if (preg_match($pattern, $product_kind) !== 1) {//整数かどうかチェック
        set_err_msg('商品の種類の入力に誤りがあります');
    }
}

/*
価格のチェック
*/
function check_price($new_price) {
    $pattern = '/^[0-9]*$/';
    if ($new_price === '') {
        set_err_msg('価格を入力してください');
    } else if (preg_match($pattern, $new_price) !== 1) {//整数かどうかチェック
        set_err_msg('価格の入力に誤りがあります');
    }
}

/*
セッションでなにかしら取得する関数
*/
function get_session($name) {//
  if (isset($_SESSION[$name]) === true) {//nameはセッション箱に存在してるんか？===してる！
    return $_SESSION[$name];//してる！なら$_SESSION[$name]使っていいで
  };
  return '';//してへんかったら空っぽ配列返す
}

/*
さていよいよセッション箱に入れましょか関数
*/
function set_session($name, $value) {//
  $_SESSION[$name] = $value;//セッション箱に入れる何かしらの名前取得したら、$valueっていう何かしらのあだ名つける
}

/*
セッションにエラーを入れる
*/
function set_err_msg($err_msg) {//エラーかましてきたらどうすんの？関数
  $_SESSION['err_msgs'][] = $err_msg;//セッション箱のerr_msgのなかに入れて、$err_msgっていうあだ名つける
}

/*
セッションに入ってるエラーを持ってくる
*/
function get_err_msgs() {//エラーたちをもらうときの関数
  $err_msgs = get_session('err_msgs');//
  if ($err_msgs === '') {//もしエラーメッセージが入ってなかったら
    return array();//ということはつまりエラーメッセージが入ってたら次のリターンまで行く
  }
  set_session('err_msgs',  array());//
  return $err_msgs;//エラーメッセージを吐く
}

/*
err_msgはセッションに存在してて、かつ、err_msgが1個でもセッションに持ってるやん！な状態
*/
function has_error(){//エラーあるぞ！関数
  return isset($_SESSION['err_msgs']) && count($_SESSION['err_msgs']) !== 0;//
}

/*
セッションに成功メッセージを入れる
*/
function set_scc_msg($scc_msg) {//セッションにメッセージ入れたいときの関数
  $_SESSION['scc_msgs'][] = $scc_msg;//セッション箱の'__messagesの中に、$messageを入れる
}

/*
セッションから成功メッセージを持ってくる
*/
function get_scc_msgs() {//メッセージたちを手に入れたときの関数
  $scc_msgs = get_session('scc_msgs');//セッション箱にいれたメッセージたちには$messagesっていうあだ名つける
  if ($scc_msgs === '') {//でもそれが空っぽやったら
    return array();//なかったことにする
  }
  set_session('scc_msgs',  array());//「セッションにメッセージ入れたいときの関数」再び
  return $scc_msgs;//$messagesっていうあだ名つける
}

