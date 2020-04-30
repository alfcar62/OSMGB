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
<div align="center">
<h1>  Il progetto <br></h1>
<br>
<h3>Guarda il video di presentazione del progetto </h3>
<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/lj0iqdUjjAA" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

<br><br>
<h3>Apri la pagina Wiki ed entra nel mondo di questo progetto con un semplice click.
</h3>
<br>
<A HREF="https://wiki.openstreetmap.org/wiki/OsmGuineaBissau_Avogadro" target='new'> <IMG SRC="../img/wikiosm.png"  ALT="la nostra pagina Wiki"></A>

</div>

</body>
</html>