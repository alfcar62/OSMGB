<?php
//aggiunta la paginazione della tabella delle morance

$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
isLogged();
?>

<html>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); 
?>
<?php
$util = $config_path .'/../db/db_conn.php';
require $util;
?>

<br>
<h2><center>Statistiche per zona<IMG SRC='../img/inserisci2.png'></center></h2>

<div style="float:left; display:block; width:350px; height:50px; ">
<form name="form" id="form" action="utility_stat.php" method="post" >
<label for="zona"> zona:</label>
<select name="zona_richiesta">
<option value="nord">nord</option>
<option value="ovest">ovest</option>
<option value="sud">sud</option>
</select>
</div>
<div style="float:left; display:block; width:350px; height:50px; ">
<label for="zona">tipo:</label>
<select name="valore">
<option value="maschi">maschi e femmine</option>
<option value="maggiorenni">maggiorenni</option>
<option value="fertili">fertili</option>
<option value="fasce">fasce</option>
<option value="abitanti">numero persone</option>
<option value="matricolati">studenti immatricolati</option>
</select>
<input type='submit' class='button' name='invia'>
</form>
</div>
<div style='clear:both;'></div>

</body>
</html>
