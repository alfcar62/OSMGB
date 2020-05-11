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
</div>
<br><br>
<br>

<div align="center">Leggi gli articoli sul progetto<br>
 <A HREF="https://wiki.openstreetmap.org/wiki/OsmGuineaBissau_Avogadro" target='new'> <IMG SRC="../img/wikiosm.png"  alt="la nostra pagina Wiki"></A>

<A HREF="https://www.diregiovani.it/2020/04/20/309317-torino-studenti-creano-anagrafe-digitale-per-guinea-bissau.dg/" target='new'> <IMG SRC="../img/diregiovani.jpg"  alt="dicono di noi"></A>

<A HREF="https://www.wikimedia.it/classi-resistenti-e-progetti-wikimedia-da-torino-alla-guinea-bissau-con-openstreetmap/
" target='new'> <IMG SRC="../img/wikimedia.jpg"  alt="dicono di noi"></A>

<br>
<A HREF="https://www.storiedialternanza.it/"> <IMG SRC="../img/storiedialternanza.jpg"  alt="concorso storiedialternanza"></A>
<A HREF="sintesi_OSMGB.pdf" download> <IMG SRC="../img/pdf.png"></A>

</div>
</body>
</html>