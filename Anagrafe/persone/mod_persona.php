<?php
/*
*** modifica_persona.php
*** 14/3/2020: A.Carlone:  correzioni varie
*** 2/03/2020  Gobbi Dennis Arneodo Alessandro: inserimento dei dati nelle tabelle storico
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
$pag=$_SESSION['pag_p']['pag_p'];
//unset($_SESSION['pag_p']);
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>
<?php
echo  " <br>MODIFICA PERSONA <br>"; 
$id_pers = $_POST['id_pers'];
echo "id pers = ". $id_pers;
$_SESSION["id_persona_modifica"]= $_POST['id_pers'];

//$result = $conn->query("START TRANSACTION");
$conn->begin_transaction();
$query = "SELECT * FROM persone WHERE id=$id_pers FOR UPDATE";	// gestione concorrenza
$result=$conn->query($query);
if (!$result)
 {
   $msg_err = "Errore select for update";
   echo $conn->error;
 }

//echo $query;

$query = "SELECT p.nominativo, p.data_nascita, p.data_morte, pc.id_casa as id_casa,";
$query .= " rpf.cod as cod_ruolo, rpf.descrizione as desc_ruolo ";
$query .=  " FROM  persone p  INNER JOIN  pers_casa pc  ON p.id=pc.id_pers ";
$query .= " INNER JOIN  ruolo_pers_fam rpf  ON pc.cod_ruolo_pers_fam= rpf.cod ";
$query .= " WHERE  p.id=$id_pers ";

//echo $query;

$result = $conn->query($query);
$nr=$result->num_rows;
if($nr==1)
{
 $row = $result->fetch_array();
 $id_casa_mod = $row['id_casa'];
 $cod_ruolo_mod = $row['cod_ruolo'];

 echo "<form action='modifica_persona.php' method='post'>";
 echo  " Nominativo : <input type='text' name='nominativo' value ='". $row['nominativo']."'<br><br>";
 echo  " Data nascita : <input type='date' name='data_nascita' value = '".$row['data_nascita']."'><br>";
 echo  " Data morte : <input type='date' name='data_morte' value = '".$row['data_morte']."'><br>";
 
 $query = "SELECT id, nome FROM casa c";
//echo $query;

 $result = $conn->query($query);
 $nr=$result->num_rows;
 echo  "Residente nella casa:";
 echo "<select name='id_casa_modifica'>";
 for($i=0;$i<$nr;$i++)
   {
     $row = $result->fetch_array();
	 if($id_casa_mod == $row["id"])
			echo "<option value='".$row["id"]."' selected>". $row["nome"]." </option>";
		else
			echo "<option value='".$row["id"]."'>".$row["nome"]." </option>";
   }
 echo "</select><br>";

 $query = "SELECT distinct cod, descrizione  FROM ruolo_pers_fam";
 $result = $conn->query($query);
 $nr=$result->num_rows;

 echo   "Ruolo nella famiglia: ";
 echo "<select name='id_ruolo_modifica'>";

 for($i=0;$i<$nr;$i++)
   {
     $row = $result->fetch_array();
	 if($cod_ruolo_mod == $row["cod"])
			echo "<option value='".$row["cod"]."' selected>". $row["descrizione"]." </option>";
		else
			echo "<option value='".$row["cod"]."'>".$row["descrizione"]." </option>";
   }
 echo "</select><br>";

 echo "<button type='submit' >invia</button>";
 echo "</form>";
 }
else 
 {
	echo "mancano le specifiche per poterla modificare";
 }
    echo "<br><a href='gest_persone.php?pag=$pag'>Torna a gestione persone</a>" 
?>

</body>
</html>