<?php
$config_path = __DIR__;
$util1="../util.php";
$util2="../db/db_conn.php";
require_once $util2;
require_once $util1;
setup();
isLogged("utente");
$pag=$_SESSION['pag_c']['pag_c'];
unset($_SESSION['pag_c']);
?>
<html>
<?php //stampaIntestazione(); ?>
<body>
<?php //stampaNavbar(); ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>
<?php 

$id_casa=$_POST["id_casa"]; 

$query = "SELECT ";
$query .= " c.id_moranca,c.nome as nome_casa, m.id as id_moranca, m.nome as nome_moranca,";
$query .= " m.cod_zona as zona ";
$query .= " FROM casa c ";
$query .= " INNER JOIN morance m ON  c.id_moranca = m.id";
$query .= " WHERE c.id = $id_casa ";

//echo $query;

$result = $conn->query($query);
$nr = $result->num_rows;

$row = $result->fetch_array();
$nome_casa = $row['nome_casa'];
$nome_moranca= $row['nome_moranca'];
$id_moranca= $row['id_moranca'];
$zona= $row['zona'];

$result->free();


$query = "SELECT ";
$query .= " p.id, p.nominativo, p.sesso, p.data_nascita,";
$query .= " c.id_moranca,c.nome nome_casa, m.nome nome_moranca,";
$query .= " m.cod_zona,  c.id_casa_moranca, c.id_osm, ";
$query .= " pc.cod_ruolo_pers_fam, rpf.descrizione,";
$query .= " p.data_inizio_val, p.data_fine_val ";
$query .= " FROM persone p";
$query .= " INNER JOIN pers_casa pc ON  pc.id_pers = p.id";
$query .= " INNER JOIN casa c ON  pc.id_casa = c.id";
$query .= " INNER JOIN morance m ON  c.id_moranca = m.id";
$query .= " INNER JOIN ruolo_pers_fam rpf ON  pc.cod_ruolo_pers_fam = rpf.cod";
$query .= " WHERE p.data_fine_val IS NULL" ;   
$query .= " AND pc.data_fine_val IS NULL ";
$query .= " AND c.data_fine_val IS NULL ";
$query .= " AND m.data_fine_val IS NULL ";
$query .= " AND pc.id_casa = $id_casa ";
$query .= " ORDER BY nominativo ASC";

//echo $query;

$result = $conn->query($query);
echo "<h3> ELENCO ABITANTI DELLA CASA: ";
echo "$nome_casa (id=$id_casa) della moranca:$nome_moranca  (id=$id_moranca)  </h3>";

$nr = $result->num_rows;

if ($nr != 0)
 {
	echo "<table border>";
    echo "<tr>";  
	echo "<th>nominativo</th>";
	echo "<th>sesso</th>";		
	echo "<th>data nascita</th>";
    echo "<th>cod ruolo fam</th>";
	echo "<th>descrizione</th>";
    echo "<th>Modifica</th>";
    echo "<th>Elimina</th>";
    
	echo "</tr>";
      
    echo "<tr>";
    while ($row = $result->fetch_array())
    {  
		$mystr = utf8_encode ($row['nominativo']) ;
		echo "<td>$mystr</td>";
		echo "<td>$row[sesso]</td>";
		echo "<td>$row[data_nascita]</td>";
		echo "<td>$row[cod_ruolo_pers_fam]</td>";
		echo "<td>$row[descrizione]</td>";
        echo " <form method='post' action='../persone/mod_persona.php'>";
	    echo "<th><button class='btn center-block' name='id_pers'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";

        echo " <form method='post' action='../persone/del_persona.php'>";
	    echo "<th><button class='btn center-block' name='id_pers'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";	
	    echo "</tr>";
    } 
	  echo "</table>";
	}
	else
        echo " Nessuna persona &egrave; presente.";

  $result->free();
  $conn->close();	

   echo "<br><a href='gest_case.php?pag=$pag'>Torna a gestione case</a>" 

 ?>
 <br>
 
 </body>
</html>