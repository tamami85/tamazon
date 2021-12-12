<?php
$err_msg = array();

$host = 'localhost';//たまのパソコン
$username = 'codecamp45262';
$password = 'codecamp45262';
$dbname = 'codecamp45262';
$charset = 'utf8';

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    try {
        $sql = 'SELECT
                    user_name, create_datetime
                FROM
                    ec_user';
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $err_msg[] = '取得できひんかったで';
    }
} catch (PDOException $e) {
    echo '接続できませんでした。理由：'.$e->getMessage();            }

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>ユーザー管理ページ</title>
        <link type="text/css" rel="stylesheet" href="./.css">
            <style>
                section {
                    margin-bottom: 20px;
                    border-top: solid 1px;
                }
        
                table {
                    width: 660px;
                    border-collapse: collapse;
                }
        
                table, tr, th, td {
                    border: solid 1px;
                    padding: 10px;
                    text-align: center;
                }
        
                caption {
                    text-align: left;
                }
            </style>
    </head>
    <body>
        <h1>tamazon ユーザー管理ページ</h1>
            <a href="tool.php" target="_blank">tamazon商品管理ページへGOO</a>
        <h2>ユーザ情報一覧</h2>
        <table>
            <tr>
                <th>ユーザID</th>
                <th>登録日</th>
            </tr>
<?php foreach($rows as $value) { ?>
            <tr>
                <td><?php print htmlspecialchars($value['user_name'] , ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php print htmlspecialchars($value['create_datetime'] , ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
<?php } ?>
        </table>
    </body>
</html>