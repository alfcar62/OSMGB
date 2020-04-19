<?php
/*
*** vis_moranca_sto.php
*** visualizzazione dati storici della moranca
*** 14/3/2020: A.Carlone: correzioni varie
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
   
$id_moranca=$_POST['id_moranca'];
// Creo una variabile dove imposto il numero di record 
// da mostrare in ogni pagina
$x_pag = 10;

// Recupero il numero di pagina corrente.
// Generalmente si utilizza una querystring
$pag = isset($_GET['pag']) ? $_GET['pag'] : 1;

// Controllo se $pag è valorizzato e se è numerico
// ...in caso contrario gli assegno valore 1
if (!$pag || !is_numeric($pag)) $pag = 1; 

$query = "SELECT count(id) as cont FROM morance_sto ";
$result = $conn->query($query);
$row = $result->fetch_array();
$all_rows= $row['cont'];
//echo $query;
    
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
</script>
<?php

echo "<h2>Situazione attuale della moranca</h2>";

// visualizzazione situazione attuale

$query = "SELECT ";
$query .= " m.id as id_moranca, m.nome as nome_moranca, z.nome as zona,m.id_mor_zona as id_mor_zona,";
$query .= " m.id_osm as id_osm,";
$query .= " m.data_inizio_val as data_inizio_val, m.data_fine_val as data_fine_val";
$query .= " FROM morance m, zone z ";
$query .= " WHERE  m.cod_zona = z.cod ";
$query .= " AND m.id = $id_moranca";

$result = $conn->query($query);
//echo $query;
if ($result->num_rows ==1)
 {
  while ($row = $result->fetch_array())
  { 
	echo "<table border>";
    echo "<tr>";
    echo "<th>id moranca</th>";
	echo "<th>id moranca-zona</th>";
	echo "<th>nome</th>";
	echo "<th>zona</th>";
	echo "<th>sulla mappa</th>";
	echo "<th>data_inizio_val</th>";
	echo "<th>data_fine_val</th>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>$row[id_moranca]</td>";
	echo "<td>$row[id_mor_zona]</td>";
	echo "<td>".utf8_encode ($row['nome_moranca'])."</td>";
    echo "<td>$row[zona]</td>";
	// va sulla mappa OSM con id_OSM
    $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
    if ($row['id_osm'] != null && $row['id_osm'] != "0")
      { 
        echo "<td>idOSM=$row[id_osm]". " <a href=$osm_link target=new> <i class='fa fa-map-marker' title ='vai sulla mappa'></i></a></td>"; 	   
      }
     else
      { 
        echo "<td>&nbsp;</td>";
      }  
	echo "<td>$row[data_inizio_val]</td>";
	echo "<td>$row[data_fine_val]</td>";
    echo "</tr>";
   }
  echo "</table>";
 }
 echo "<p>";


// visualizzazione situazione storica

echo "<h2>Storia della moranca</h2>";
      
$query =  "SELECT tipo_op, id_moranca, id_mor_zona,id_osm,";
$query .= " nome as nome_moranca, cod_zona, data_inizio_val, data_fine_val ";
$query .= " FROM morance_sto ";
$query .= " WHERE  id_moranca = $id_moranca ";
$query .= " ORDER BY id ASC, data_fine_val DESC";
$query .= " LIMIT $first, $x_pag";

//echo $query;
$result = $conn->query($query);

echo "<table border>";
  
if ($result->num_rows !=0)
	{
		echo "<tr>";
		echo "<th>tipo modifica</th>";
	    echo "<th>id moranca</th>";
	    echo "<th>id moranca-zona</th>";
	    echo "<th>nome moranca</th>";
		echo "<th>zona</th>";
		echo "<th>sulla mappa</th>";
        echo "<th>data inizio_val</th>";
		echo "<th>data fine val</th>";
		echo "</tr>";

	    while ($row = $result->fetch_array())
		 {
			echo "<tr>";
			echo "<td>". $row['tipo_op']. "</td>";
			echo "<td>". $row['id_moranca']."</td>";
			echo "<td>". $row['id_mor_zona']."</td>";
	        echo "<td>".utf8_encode ($row['nome_moranca'])."</td>";
			echo "<td>". $row['cod_zona']."</td>";
			
			 // va sulla mappa OSM con id_OSM
          $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
          if ($row['id_osm'] != null && $row['id_osm'] != "0")
            { 
              echo "<td>idOSM=$row[id_osm]". " <a href=$osm_link target=new> <i class='fa fa-map-marker' title ='vai sulla mappa'></i></a></td>"; 	   
            }
           else
            { 
              echo "<td>&nbsp;</td>";
            }  
		    echo "<td>". $row['data_inizio_val']."</td>";
            echo "<td>". $row['data_fine_val']."</td>";
			echo "</tr>";
		 }
	   echo "</table>";
	}
	else
		echo "Non vi sono state variazioni per la moranca.";
  echo "<br> Numero operazioni: $all_rows<br>";

   // visualizza pagine
   $vis_pag = $config_path .'/../vis_pag.php';
   require $vis_pag;


  $result->free();
  $conn->close();	
 ?>  
 
 </body>
</html>