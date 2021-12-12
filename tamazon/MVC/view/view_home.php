<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>tamazonでお買い物</title>
        <link rel="stylesheet" href="../tamazon_css/tamazon_home.css">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
        <!--↓グーグルからジャバスクのjquery呼び出し-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <!--↓ネット上にあるスライドをしてくれるファイルを呼び出す-->
        <script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script>
        <script type="text/javascript">
                $(document).ready(function(){
                    // ↓スライダーを定義
                    $('.slider').bxSlider({
                        // ↓5秒ごとにスライドする
                        auto: true,
                        pause: 5000,
                    });
                });
        </script>
        <script type="text/javascript">
            $(function(){
                // ↓フッターに入れる変数を定義
                var $ftr = $('#footer');
                // ↓もしサイトの高さがコンテンツが少ないせいで低くなってもフッターはいっちゃん下にするで
                if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
                    // ↓コンテンツ領域の高さーフッターの高さにposition:fixedをつける
                    // position:fixedは画面の決まった位置に要素を固定させたいときに使う。CSSのやつ
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
        
        <section>
            <!--↓変数sliderのやつ呼び出してこの画像流す-->
            <div class="slider">
                <div class="comment"><img class="slider_img" src="../decoration.img/ldk.png"><p>family</p></div>
                <div class="comment"><img class="slider_img" src="../decoration.img/mountain_house.png"><p>HOUSE</p></div>
                <div class="comment"><img class="slider_img" src="../decoration.img/wood_kitchen.png"><p>kitchen</p></div>
            </div>
        </section>
        
        <article>
            <h2>PICK UP!</h2>
            
<?php include 'template_msg.php'; ?>

            <form method="get">
                <select name="product_kind" required>
                    <option value="">選択してください</option>
                    <option value="0">kitchen</option>
                    <option value="1">bathroom&restroom</option>
                    <option value="2">bedroom</option>
                    <option value="3">outdoor</option>
                    <option value="4">others</option>
                </select>
                <input type="submit" name="search" value="SEARCH!">
            </form>

            <div class="flex_container">
<?php foreach ($item_details as $value) { //ここからforeach?>
                <div class="container">
                    <div><img class="img_size" src="<?php print $img_dir . entity_str($value['img']); ?>"></div>
                    <div><?php print entity_str($value['name']); ?></div>
                    <div>￥<?php print entity_str($value['price']); ?></div>
    <?php if ($value['product_kind'] === 0) { ?>
                    <div class="blue">kitchen</div>
    <?php } ?>
    <?php if ($value['product_kind'] === 1) { ?>
                    <div class="blue">bathroom&restroom</div>
    <?php } ?>
    <?php if ($value['product_kind'] === 2) { ?>
                    <div class="blue">bedroom</div>
    <?php } ?>
    <?php if ($value['product_kind'] === 3) { ?>
                    <div class="blue">outdoor</div>
    <?php } ?>
    <?php if ($value['product_kind'] === 4) { ?>
                    <div class="blue">others</div>
    <?php } ?>
    <?php if ($value['stock'] > 0) {//ストックがあったらカートボタン ?>
                    <form method="post">
                        <input type="submit" value="カートに追加">
                        <input type="hidden" name="item_id" value="<?php print entity_str($value['item_id']); ?>">
                        <input type="hidden" name="sql_kind" value="insert_cart">
                    </form>
    <?php } else { //ストックなかったら売り切れ表示?>
                    <div class="glay">sold out</div>
    <?php } ?>
                </div>
<?php } //ここまでforeach?>
            </div>
        </article>
            
        <div><a href="cart.php"><input type="submit" value="買い物かごへGO"></a></div>
        
        <!--↓上で作ったfooterの関数はここで実行-->
        <footer id="footer">
            <p>Copyright &copy; tamazon All Rights Reserved.</p>
        </footer>
    </body>
</html>