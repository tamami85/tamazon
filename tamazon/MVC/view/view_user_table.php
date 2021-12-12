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
            <a href="tool.php" target="_blank">tamazon商品管理ページへGO</a>
        <h2>ユーザ情報一覧</h2>
        <table>
            <tr>
                <th>ユーザID</th>
                <th>登録日</th>
            </tr>
<?php foreach($user_data as $value) { ?>
            <tr>
                <td><?php print entity_str($value['user_name']); ?></td>
                <td><?php print entity_str($value['create_datetime']); ?></td>
            </tr>
<?php } ?>
        </table>
    </body>
</html>