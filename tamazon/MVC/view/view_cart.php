<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>tamazonのカート</title>
        <link rel="stylesheet" href="../tamazon_css/tamazon_cart.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            $(function(){
                var $ftr = $('#footer');
                if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
                    $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px;' });
                }
            });
        </script>
    </head>
    <body>
        <header>
            <h1>tamazon<img src="../decoration.img/cat_hand.png"></h1>
                <div class="flex">
                    <p>welcome!&nbsp;<?php print entity_str($user_name); ?></p>
                    <a href="cart.php"><img src="../decoration.img/48cart.png"></a>
                </div>
                <a href="logout.php"><p>logout</p></a>
        </header>
        <h2>これ買う<span class="blue">&#063;</span></h2>
        
<?php include 'template_msg.php'; ?>
        
        <artcile>
<?php foreach ($cart_data as $value) { ?>
            <div class="background_white">
                
                <div><img class="img_size" src="<?php print $img_dir . entity_str($value['img']); ?>"></div>
                <div><?php print entity_str($value['name']); ?></div>
                <div>￥<?php print entity_str($value['price']); ?></div>
                <form method="post">
                    <input type="text" name="amount" value="<?php print entity_str($value['amount']); ?>">
                    <input type="submit" value='変更'>
                    <input type="hidden" name="item_id" value="<?php print entity_str($value['item_id']); ?>">
                    <input type="hidden" name="sql_kind" value="change_amount">
                </form>
                <form method="post">
                    <input type="submit" name="delete_cart" value="削除">
                    <input type="hidden" name="item_id" value="<?php print entity_str($value['item_id']); ?>">
                    <input type="hidden" name="sql_kind" value="delete">
                </form>
                
            </div>
<?php } ?>
            <p>合計￥<?php print entity_str($cal_result); ?></p> 
            <form method="post" action="result.php">
                <input type="submit" name="buy" value="買う！">
            </form>
        </artcile>
        <p><a href="home.php">homeに戻る</a></p>
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>