<ul>
<?php $errors = get_err_msgs(); ?>
<?php foreach ($errors as $value) { //エラーメッセージ表示?>
    <li><img src="../decoration.img/15cat.png"><?php print $value; ?><img src="../decoration.img/15cat.png"></li>
<?php } ?>
<?php $successes = get_scc_msgs(); ?>
<?php foreach ($successes as $value) { //成功メッセージ表示?>
    <li class="blue"><img src="../decoration.img/15cat.png"><?php print $value; ?><img src="../decoration.img/15cat.png"></li>
<?php } ?>
</ul>

