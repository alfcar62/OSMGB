<?php
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
setup();
isLogged();
?>
<html>
<?php //stampaIntestazione(); ?>
<body>
<?php //stampaNavbar(); ?>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("backend-search_pers.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
                resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
    });
    
    // Set search input value on click of result item
    $(document).on("click", ".result p", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".result").empty();
    });
});
</script>
 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php stampaIntestazione(); ?>
<?php stampaNavbar(); ?>
    <div class="search-box">
        <input type="text" autocomplete="off" placeholder="Ricerca persona..." />
        <div class="result"></div>
    </div>
<?php 
// Creo una variabile dove imposto il numero di record 
// da mostrare in ogni pagina
$x_pag = 10;
// Recupero il numero di pagina corrente.
// Generalmente si utilizza una querystring
$pag = isset($_GET['pag']) ? $_GET['pag'] : 1;

// Controllo se $pag ? valorizzato e se ? numerico
// ...in caso contrario gli assegno valore 1
if (!$pag || !is_numeric($pag)) $pag = 1; 

// Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella agenda
$query2 = "SELECT count(id) as cont FROM persona";
$result = $conn->query($query2);
$row = $result->fetch_array();
//esiste la count
$all_rows= $row['cont'];

    
//  definisco il numero totale di pagine
$all_pages = ceil($all_rows / $x_pag);

// Calcolo da quale record iniziare
$first = ($pag - 1) * $x_pag;
echo $first;
echo $x_pag;
    $selectedRole=$_POST["stat_ruolo"];
    $query3="select count(id) as conta from persona where sesso='m';";
$query2= "SELECT persona.id,persona.nominativo,persona.sesso,persona.data_nascita ,famiglia.nome,pers_fam.ruolo_pers_fam from persona inner join pers_fam on persona.id=pers_fam.id_pers inner join famiglia on id_fam=famiglia.ID WHERE pers_fam.ruolo_pers_fam='$selectedRole' ORDER BY persona.nominativo asc LIMIT $first, $x_pag";
/*$query = "SELECT ";
$query .= " p.id, p.nominativo, p.sesso, p.data_nascita,";
$query .= "  pf.id_fam, c.id_moranca, m.nome nome_moranca,";// pc.id_casa
$query .= " m.cod_zona,  c.id_moranca, c.id_osm, ";
$query .= " p.data_inizio_val, p.data_fine_val ";
$query .= " FROM persona p";
$query .= " INNER JOIN pers_fam pf ON  pf.id_pers = p.id";
//$query .= " INNER JOIN pers_casa pc ON  pc.id_pers = p.id";
$query .= " LEFT OUTER JOIN casa c ON  pc.id_casa = c.id";
$query .= " LEFT OUTER JOIN moranca m ON  c.id_moranca = m.id";
$query .= " WHERE p.data_fine_val IS NULL" ;   
$query .= " AND pf.data_fine_val IS NULL ";
$query .= " AND pc.data_fine_val IS NULL ";
$query .= " AND c.data_fine_val IS NULL ";
$query .= " AND m.data_fine_val IS NULL ";
$query .= " ORDER BY nominativo ASC";
$query .= " LIMIT $first, $x_pag";
*/
$result = $conn->query($query2);
    $result2=$conn->query($query3);
    $row2=$result2->fetch_array();
    $nrMaschi=$row2['conta'];
    echo "<br>".$nrMaschi."ciao";
echo "<h2> Villaggio di NTchangue</h2>";
echo "<h3> Elenco Abitanti</h3>";
echo $query2;
$nr = $result->num_rows;
//echo $nr;
if ($nr != 0)
 {
	echo "<table border>";
    echo "<tr>";
    
	echo "<th>nominativo</th>";
	echo "<th>sesso</th>";		
	echo "<th>data nascita</th>";
    echo "<th>ruolo fam</th>";
    echo "<th>famiglia</th>";
    
	echo "</tr>";
      
    echo "<tr>";
    while ($row = $result->fetch_array())
    {  
		echo "<td>$row[nominativo]</td>";
		echo "<td>$row[sesso]</td>";
		echo "<td>$row[data_nascita]</td>";
		echo "<td>$row[ruolo_pers_fam]</td>";
		echo "<td>$row[nome]</td>";
       
		echo "</tr>";
    } 
	  echo "</table>";
	}
	else
        echo " Nessuna persona &egrave; presente nel database.";



// Se le pagine totali sono pi? di 1...
// stampo i link per andare avanti e indietro tra le diverse pagine!
  
  echo "<br> Numero abitanti: $all_rows<br>Numero abitanti maschili:$nrMaschi<br>";
  if ($all_pages > 1){
  if ($pag > 1){
    echo "<br><a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag - 1) . "\">";
    echo "Pagina Indietro</a>&nbsp;<br><br>";
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
    echo "<br><br><a href=\"" . $_SERVER['PHP_SELF'] . "?stat_ruolo=$selectedRole>";
    echo "Pagina Avanti<br></a>";
  } 
}
echo "<a href='statistiche.php'>Torna a Statistiche</a>" ;
  $result->free();
  $conn->close();	
 ?>
 <br>
  
 
 </body>
</html>