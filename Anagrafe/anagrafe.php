<?php
$config_path = __DIR__;
$util = $config_path .'/util.php';
require $util;
isLogged();
setup();
?>
<html>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>