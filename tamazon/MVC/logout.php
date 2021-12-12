<?php
session_start();
$session_name = session_name();
$_SESSION = array();

if (isset($_COOKIE[$login_id])) {
    $params = session_get_cookie_params();

    setcookie($login_id, ''. time(), - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: login.php');
exit;
?>
