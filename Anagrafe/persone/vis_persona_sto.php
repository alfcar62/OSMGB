<?php
/*
*** vis_persona_sto: visualizzazione dati storici di una persona
*** 14/3/2020: A.Carlone: correzioni varie
*** 02/03/20  Gobbi Dennis 
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
isLogged("gestore");
?>
<html>
    <link rel="stylesheet" type="text/css" href="../css/style.css">

       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php stampaIntestazione(); ?>
<body onload="myFunction()">
<?php stampaNavbar(); 

$id_persona=$_POST['id_persona'];
// Creo una variabile dove imposto il numero di record 
// da mostrare in ogni pagina
$x_pag = 10;

// Recupero il numero di pagina corrente.
// Generalmente si utilizza una querystring
$pag = isset($_GET['pag']) ? $_GET['pag'] : 1;

// Controllo se $pag è valorizzato e se è numerico
// ...in caso contrario gli assegno valore 1
if (!$pag || !is_numeric($pag)) $pag = 1; 

// Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella agenda
$query = "SELECT count(id) as cont FROM persone_sto where id_persona='$id_persona'";
$result = $conn->query($query);
    echo $conn->error;
$row = $result->fetch_array();
$all_rows= $row['cont'];

    
//  definisco il numero totale di pagine
$all_pages = ceil($all_rows / $x_pag);

// Calcolo da quale record iniziare
$first = ($pag - 1) * $x_pag;

?>
<script>
function myFunction(){ //funzione per visualizzare un div (con una select dentro)quando si seleziona "modifica"
var e = document.getElementById("tipo_operazione");
var b=document.getElementById("div_invisibile");
var selezionato = e.options[e.selectedIndex].text;
if(selezionato=="Modifica")
    b.style.visibility="visible";
else
    b.style.visibility="hidden"; 
}
</script>
<?php

echo "<h2>Situazione attuale della persona</h2>";

// visualizzazione situazione attuale
      
$query =" SELECT ";
$query.=" p.id,";
$query.=" p.nominativo,";
$query.=" sesso, matricola_stud,";
$query.=" data_nascita,data_morte,";
$query.=" c.id as id_casa, c.nome as nome_casa,";
$query.=" pc.cod_ruolo_pers_fam, rpf.descrizione as desc_ruolo_pers_fam,s.matricola";
$query.=" FROM ";
$query.=" persone p LEFT JOIN studenti s ON  s.matricola = p.matricola_stud,";
$query.= "pers_casa pc, casa c, ruolo_pers_fam rpf";   
$query.= " WHERE id_pers = $id_persona";
$query.= " AND p.id = pc.id_pers";
$query.= " AND c.id = pc.id_casa";
$query.= " AND rpf.cod = pc.cod_ruolo_pers_fam";
$result = $conn->query($query);
//echo $query;
if ($result->num_rows ==1)
 {
 while ($row = $result->fetch_array())
 {
    echo "<table border>";
    echo "<tr>";
    echo "<th>id persona</th>";
    echo "<th>nominativo</th>";
    echo "<th>sesso</th>";
    echo "<th>data nascita</th>";
    echo "<th>data morte</th>";
    echo "<th>id casa</th>";
    echo "<th>nome_casa</th>";
    echo "<th>cod ruolo</th>";
    echo "<th>desc ruolo</th>";
     echo "<th>matricola</th>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>$row[id]</td>";
    echo "<td>".utf8_encode ($row['nominativo'])."</td>";
    echo "<td>$row[sesso]</td>";
    echo "<td>$row[data_nascita]</td>";
    echo "<td>$row[data_morte]</td>";
    echo "<td>$row[id_casa]</td>";
    echo "<td>$row[nome_casa]</td>";
    echo "<td>$row[cod_ruolo_pers_fam]</td>";
    echo "<td>$row[desc_ruolo_pers_fam]</td>";
     echo "<td>$row[matricola]</td>";
    echo "</tr>";
    echo "</table>";
   }
 }
 echo "<p>";
// visualizzazione situazione storica

echo "<h2>Storia della persona</h2>";

if (isset($_POST['tipo_operazione']))
   $tipo_operazione = $_POST['tipo_operazione'];
if (isset($_POST['valore_operazione']))
   $valore_operazione = $_POST['valore_operazione'];
      
$query = "SELECT tipo_op,";
$query.=" cod_ruolo_pers_fam,";
$query.=" id_persona,";
$query.=" nominativo,";
$query.=" sesso, matricola_stud,";
$query.=" data_nascita,data_morte,";
$query.=" id_casa, nome_casa,";
$query.=" cod_ruolo_pers_fam, desc_ruolo_pers_fam,";
$query.=" data_inizio_val,";
$query.=" data_fine_val, ";
$query.=" matricola_stud ";
$query.=" FROM ";
$query.=" persone_sto "; 
$query.= " WHERE id_persona = $id_persona";
$query .= " ORDER BY id DESC,data_fine_val DESC";
$query .= " LIMIT $first, $x_pag";
$result = $conn->query($query);
//echo $query;
if ($result->num_rows !=0)
	{
   		echo "<table border>";
		echo "<tr>";
		echo "<th>operazione</th>";
		echo "<th>data inizio val</th>";
        echo "<th>data fine val</th>";
		echo "<th>id persona</th>";
		echo "<th>nominativo</th>";
		echo "<th>sesso</th>";
		echo "<th>data nascita</th>";
		echo "<th>data morte</th>";
		echo "<th>id casa</th>";
		echo "<th>nome casa</th>";
		echo "<th>cod ruolo</th>";
		echo "<th>desc ruolo</th>";
        echo "<th>matricola</th>";
	    echo "</tr>";

	    while ($row = $result->fetch_array())
		 {
			echo "<tr>";
			echo "<td>$row[tipo_op]</td>";
			echo "<td>$row[data_inizio_val]</td>";	
            echo "<td>$row[data_fine_val]</td>";
			echo "<td>$row[id_persona]</td>";
			echo "<td>".utf8_encode ($row['nominativo'])."</td>";
			echo "<td>$row[sesso]</td>";
            echo "<td>$row[data_nascita]</td>";
            echo "<td>$row[data_morte]</td>";
            echo "<td>$row[id_casa]</td>";
            echo "<td>$row[nome_casa]</td>";
            echo "<td>$row[cod_ruolo_pers_fam]</td>";
            echo "<td>$row[desc_ruolo_pers_fam]</td>";
            echo "<td>$row[matricola_stud]</td>";
		 }
		 echo "</table>";
	}
	else
		echo " Non vi sono variazioni sulla persona";
  echo "<br> Numero operazioni: $all_rows<br>";

// visualizza pagine
  $vis_pag = $config_path .'/../vis_pag.php';
  require $vis_pag;

  $result->free();
  $conn->close();
  
  echo "<br><a href='gest_persone.php'>Torna a gestione persone</a>" 

 ?>  
 
 </body>
</html>