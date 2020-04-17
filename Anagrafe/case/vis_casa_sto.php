<?php
/*
*** vis_casa_sto.php
*** visualizzazione dati storici della casa
*** 14/3/2020: A.Carlone: correzioni varie
*** 28/02/2020: Ferraiuolo,Arneodo :aggiunta js
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
?>
<html>
<link rel="stylesheet" type="text/css" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<?php
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
isLogged("utente");
?>
<?php stampaIntestazione(); ?>
<body onload="myFunction()">
<?php stampaNavbar(); 
   
$id_casa=$_POST['id_casa'];
// Creo una variabile dove imposto il numero di record 
// da mostrare in ogni pagina
$x_pag = 10;

// Recupero il numero di pagina corrente.
// Generalmente si utilizza una querystring
$pag = isset($_GET['pag']) ? $_GET['pag'] : 1;

// Controllo se $pag è valorizzato e se è numerico
// ...in caso contrario gli assegno valore 1
if (!$pag || !is_numeric($pag)) $pag = 1; 

$query = "SELECT count(id) as cont FROM casa_sto where id_casa=$id_casa";
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

echo "<h2>Situazione attuale della casa</h2>";

// visualizzazione situazione attuale
 
 /*
 *** verifica se la casa ha un capo famiglia
 */

   $query =  "SELECT p.id as id_pers, p.nominativo as capo_famiglia ";
   $query .= " FROM persone p INNER JOIN pers_casa pc  ON p.id = pc.id_pers ";
   $query .= " INNER JOIN  casa c ON c.id = pc.id_casa ";   
   $query .= " WHERE c.id = $id_casa";
   $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";

 //  echo "q: ". $query. "<br>";
   $result = $conn->query($query);
   if (!$result)
        throw new Exception($conn->error);
   $row = $result->fetch_array();
   if ($result->num_rows >0)
	{
	   $capo_famiglia = "";
    }
   else 
	   $capo_famiglia = $row['capo_famiglia'];

$query =  " SELECT ";
$query .= " c.id as id_casa, c.nome as nome_casa, c.id_moranca, c.id_osm, c.data_inizio_val,";
$query .= " m.nome as nome_moranca";
$query .= " FROM ";
$query .="  casa c, pers_casa pc, morance m, persone p ";   
$query .= " WHERE c.id = $id_casa";
$query .= " AND m.id = c.id_moranca";
$query .= " AND c.id = pc.id_casa";
$query .= " AND p.id = pc.id_pers";

$result = $conn->query($query);
//echo $query;
if ($result->num_rows ==1)
 {
 while ($row = $result->fetch_array())
 {
    echo "<table border>";
    echo "<tr>";
    echo "<th>id casa</th>";
	echo "<th>data_inizio_val</th>";
    echo "<th>nome</th>";
    echo "<th>id_moranca</th>";
    echo "<th>moranca</th>";
    echo "<th>capo famiglia</th>";
    echo "<th>id osm</th>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>$row[id_casa]</td>";
	echo "<td>$row[data_inizio_val]</td>";
	echo "<td>$row[nome_casa]</td>";
	echo "<td>$row[id_moranca]</td>";
    echo "<td>".utf8_encode ($row['nome_moranca'])."</td>";
	echo "<td>".utf8_encode ($capo_famiglia)."</td>";
    echo "<td>$row[id_osm]</td>";
    echo "</tr>";
    echo "</table>";
   }
 }
 echo "<p>";


// visualizzazione situazione storica

echo "<h2>Storia della casa</h2>";

if (isset($_POST['tipo_operazione']))
   $tipo_operazione = $_POST['tipo_operazione'];

if (isset($_POST['valore_operazione']))
   $valore_operazione = $_POST['valore_operazione'];
      
$query =  "SELECT tipo_op, data_inizio_val, data_fine_val,";
$query .= "id_casa, nome, id_moranca, nome_moranca,";
$query .= "nome_capo_famiglia, id_osm ";
$query .= " FROM casa_sto ";
$query .= " WHERE id_casa=$id_casa";
$query .= " ORDER BY id_casa ASC, data_fine_val DESC";
$query .= " LIMIT $first, $x_pag";
//echo $query;
$result = $conn->query($query);
   
if ($result->num_rows !=0)
	{
   		echo "<table border>";
		echo "<tr>";
		echo "<th>tipo modifica</th>";
		echo "<th>id casa</th>";
		echo "<th>nome</th>";
        echo "<th>data inizio_val</th>";
		echo "<th>data fine val</th>";
		echo "<th>id moranca</th>";
		echo "<th>nome moranca</th>";
		echo "<th>capo famiglia</th>";
		echo "<th>id OSM</th>";
		echo "</tr>";

	    while ($row = $result->fetch_array())
		 {
			echo "<tr>";
			echo "<td>". $row['tipo_op']. "</td>";
			echo "<td>". $row['id_casa']."</td>";
			echo "<td>". $row['nome']."</td>";
		    echo "<td>". $row['data_inizio_val']."</td>";
            echo "<td>". $row['data_fine_val']."</td>";
			echo "<td>". $row['id_moranca']."</td>";
			$mystr = utf8_encode ($row['nome_moranca']) ;
		    echo "<td>$mystr</td>";
			echo "<td>".$row['nome_capo_famiglia']."</th>";
            		
            $osm_link = "https://www.openstreetmap.org/way/".$row['id_osm'];
            if ($row['id_osm'] != null)
             { 
			  echo "<td>".$row['id_osm']."&nbsp;<a href=$osm_link target=new>vai alla mappa&nbsp;";
			  echo "<IMG SRC=../css/osm.png WIDTH=20 HEIGHT=20 BORDER=0></a></td>"; 
		     }
		    else
             { 
              echo "<td>". $row['id_osm']."&nbsp;</td>";
             }
		 }
		 echo "</tr></table>";
	}
	else
		echo " Nessuna operazione è stata effettuata sulla casa.";
  echo "<br> Numero operazioni: $all_rows<br>";

// Se le pagine totali sono più di 1...
// stampo i link per andare avanti e indietro tra le diverse pagine!
  if ($all_pages > 1){
  if ($pag > 1){
    echo "<br><a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag - 1) . "\">";
    echo "Pagina Indietro</a>&nbsp;<br>";
  }
  // faccio un ciclo di tutte le pagine
  $cont=0;
  for ($p=1; $p<=$all_pages; $p++) 
   {
	 if ($cont>=50)
		 {
		  echo "<br>";
		  $cont=0;
         }
	  $cont++;
    // per la pagina corrente non mostro nessun link ma la evidenzio in bold
    // all'interno della sequenza delle pagine
    if ($p == $pag) echo "<b>" . $p . "</b>&nbsp;";
    // per tutte le altre pagine stampo il link
    else
	 { 
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $p . "\">";
      echo $p . "</a>&nbsp;";
     } 
  }
  if ($all_pages > $pag)
   {
    echo "<br><br><a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag + 1) . "\">";
    echo "Pagina Avanti<br></a>";
   } 
}

  $result->free();
  $conn->close();	
 ?>  
 
 </body>
</html>