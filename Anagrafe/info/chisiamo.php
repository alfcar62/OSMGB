<?php
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
unsetPag(basename(__FILE__)); 
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); 
?>
<center>
<h1> Chi siamo <br></h1>

<div align="center">
<IMG SRC="../img/gruppo.jpeg" WIDTH="30%"  BORDER="0" ALT="">
<IMG SRC="../img/lavoro2.jpeg" WIDTH="30%"  BORDER="0" ALT="">
</div>
<div align="center">
<IMG SRC="../img/lavoro3.jpeg" WIDTH="30%"  BORDER="0" ALT="">
<IMG SRC="../img/lavoro4.jpeg" WIDTH="30%"  BORDER="0" ALT="">
</div>
<div align="center">
<IMG SRC="../img/ntchangue1.jpeg" WIDTH="30%"  BORDER="0" ALT="">
<IMG SRC="../img/ntchangue2.jpeg" WIDTH="30%"  BORDER="0" ALT="">
</div>
<div align="center">
<IMG SRC="../img/ntchangue3.jpeg" WIDTH="30%"  BORDER="0" ALT="">
<IMG SRC="../img/ntchangue4.jpeg" WIDTH="30%"  BORDER="0" ALT="">
</div>
</center>
</body>
</html>