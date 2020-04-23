<?php
/*
*** ins_persona.php
*** Richiamata da gest_persone.php
*** attiva insert_persona.php
*** 15/03/2020: A.Carlone: Modifiche e correzioni varie
*** 01/03/2020:Gobbi Dennis Arneodo Alessandro
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>

<?php
echo "<h3>Inserimento di una nuova persona</h3>";
echo "<br><br>";
echo "<form action='insert_persona.php' name='form' id = 'form' method='post'>";
echo  " <label for='nominativo'>Nominativo : </label><input type='text' name='nome_persona' placeholder='inserire nome ' required><br>";
echo  " <label for='data_nascita'>Data nascita : </label><input type='date' name='data_nascita' required><br>";
echo   "<label for='sesso'>Seleziona sesso :</label> <select name='sesso' required>";
echo "<option></option><br>";
echo "<option value=m>maschio</option>";
echo "<option value=f>femmina</option>";
echo "</select><br>";


$query = "SELECT m.nome as nome_moranca, c.id as id_casa, c.nome as nome_casa,";
$query .= "p.nominativo as capo_famiglia";
$query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
$query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
$query .= "LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
$query .=" AND pc.cod_ruolo_pers_fam = 'CF'";
$query .=" LEFT JOIN persone p ON p.id = pc.id_pers";
$query .= " WHERE c.DATA_FINE_VAL is null";
$query .= " ORDER BY c.nome ASC";
$result = $conn->query($query);  

$result = $conn->query($query);

//echo $query;
/*
*** form  per la scelta della casa
*/ 
echo "<form action='ins_persona.php' name='form' id='form' method='POST'><br>";
echo   "<label for='casa'> Residente nella casa:</label> <select name='id_casa_nuova' required>";
$nr=$result->num_rows;
echo "<option></option><br>";
for($i=0;$i<$nr;$i++)
{
  $row = $result->fetch_array();
  if($row['nome_casa']!=null || $row['nome_casa']!="")
  {
	$myCapoFam = utf8_encode ($row['capo_famiglia']) ;
	$myMoranca = utf8_encode ($row['nome_moranca']) ;
    if ($myCapoFam=="")
		$myCapoFam = "Non Esiste";
    echo "<option value=".$row['id_casa'].">casa:".$row['nome_casa']." (capo famiglia:".$myCapoFam. ") moranca:".$myMoranca."  </option>";
   
  }
}
echo "</select><br>";

$result = $conn->query("SELECT distinct cod, descrizione FROM ruolo_pers_fam");
$nr=$result->num_rows;

echo   "<label for='ruolo'>Ruolo:</label> <select name='id_ruolo_nuovo' required>";
echo "<option></option><br>";

for($i=0;$i<$nr;$i++)
{
  $row = $result->fetch_array();
  if($row['cod']!=null || $row['nome_casa']!="")
  {
     echo "<option value='".$row['cod']."'>".$row['descrizione']."</option>";
  }

}
echo "</select><br>";
echo "<button type='submit' class='button'>Conferma</button>";
echo "</form>";    
?>

</body>
    
</html>