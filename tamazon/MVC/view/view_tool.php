<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>tamazon</title>
        <link rel="stylesheet" href="../tamazon_css/tamazon_tool.css">
    </head>
    <body>
        <h1>tamazon管理ツール</h1>
            <a href="user_table.php" target="_blank">tamazonユーザー管理ページへGO</a>
        <ul>
<?php foreach (get_err_msgs() as $value) { ?>
            <li><?php print $value; ?></li>
<?php } ?>
<?php foreach (get_scc_msgs() as $value) { ?>
            <li><?php print $value; ?></li>
<?php } ?>
        </ul>
        <section>
            <h2>新規商品追加</h2>
            <form method="post" enctype="multipart/form-data">
                <div><label>名前: <input type="text" name="new_name" value=""></label></div>
                <div><label>値段: <input type="text" name="new_price" value=""></label></div>
                <div><label>個数: <input type="text" name="new_stock" value=""></label></div>
                <div>
                    <select name="new_status">
                        <option value="0">非公開</option>
                        <option value="1">公開</option>
                    </select>
                </div>
                <div>
                    <select name="product_kind">
                        <option value="0">キッチン</option>
                        <option value="1">バス・トイレ</option>
                        <option value="2">寝室</option>
                        <option value="3">外</option>
                        <option value="4">その他</option>
                    </select>
                </div>
                <div><input type="file" name="new_img"></div>
                <input type="hidden" name="sql_kind" value="insert">
                <div><input type="submit" value="■□■□■商品追加■□■□■"></div>
            </form>
        </section>
        <section>
            <h2>商品情報変更</h2>
            <table>
                <caption>商品一覧</caption>
                <tr>
                    <th>商品画像</th>
                    <th>商品名</th>
                    <th>価格</th>
                    <th>在庫数</th>
                    <th>ここで便利</th>
                    <th>ステータス</th>
                    <th>操作</th>
                </tr>
<?php foreach ($item_data as $value) { ?>
        <?php if ($value['status'] === 1) { ?>
                <tr>
        <?php } else { ?>
                <tr class="status_false">
        <?php } ?>
                    <td><img src="<?php print $img_dir . entity_str($value['img']); ?>" class="img_size"></td>
                    <td class="item_name_width"><?php print entity_str($value['name']); ?></td>
                    <td class="text_align_right"><?php print entity_str($value['price']); ?>円</td>
                    <!--↓update_stockをhiddenでわける-->
                    <td>
                        <form method="post">
                            <input type="text"  class="input_text_width text_align_right" name="update_stock" value="<?php print htmlspecialchars($value['stock'] , ENT_QUOTES, 'UTF-8'); ?>">個&nbsp;&nbsp;<input type="submit" value="変更">
                            <input type="hidden" name="item_id" value="<?php print entity_str($value['item_id']); ?>">
                            <input type="hidden" name="sql_kind" value="update">
                        </form>
                    </td>
        <?php if ($value['product_kind'] === 0) { ?>
                    <td>キッチン</td>
        <?php } ?>
        <?php if ($value['product_kind'] === 1) { ?>
                    <td>バス・トイレ</td>
        <?php } ?>
        <?php if ($value['product_kind'] === 2) { ?>
                    <td>寝室</td>
        <?php } ?>
        <?php if ($value['product_kind'] === 3) { ?>
                    <td>外</td>
        <?php } ?>
        <?php if ($value['product_kind'] === 4) { ?>
                    <td>その他</td>
        <?php } ?>
                    <td>
                        <form method="post">
        <?php if ($value['status'] === 1) { ?>
                            <input type="submit" value="公開 → 非公開">
                            <input type="hidden" name="change_status" value="0">
        <?php } else { ?>
                            <input type="submit" value="非公開 → 公開">
                            <input type="hidden" name="change_status" value="1">
        <?php } ?>
                            <input type="hidden" name="item_id" value="<?php print entity_str($value['item_id']); ?>">
                            <input type="hidden" name="sql_kind" value="change">
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="submit" name="delete_item" value="削除">
                            <input type="hidden" name="item_id" value="<?php print entity_str($value['item_id']); ?>">
                            <input type="hidden" name="sql_kind" value="delete">
                        </form>
                    </td>
                </tr>
<?php } ?>
            </table>
        </section>
    </body>
</html>