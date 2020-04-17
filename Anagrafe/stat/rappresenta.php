<?php
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
isLogged();
setup();
//Data ultima modifica:1/03/2020    Autore:Gobbi Dennis
//Descrizione:modifica della query che stampa la tabella(non stampa le persone con data_fine_val a null)
//Modifica barra di ricerca dinamica(attualmente non funzionante e quindi commentato)
?>

<html>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
       // $.post("mostra_persone-search_bar.php",{nome:$(this).text()});
        //window.location.href="gest_persone.php";
        $(this).parent(".result").empty();
    });
});
</script>
 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); ?>
    <div class="search-box">
        <input type="text" autocomplete="off" placeholder="Ricerca persona..." />
        <div class="result"></div>
    </div>

<?php
// Paginazione:
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
$query2 = "SELECT count(id) as cont FROM persone";
$result = $conn->query($query2);
$row = $result->fetch_array();
//esiste la count
$all_rows= $row['cont'];

    
//  definisco il numero totale di pagine
$all_pages = ceil($all_rows / $x_pag);

// Calcolo da quale record iniziare
$first = ($pag - 1) * $x_pag;

 echo "<h3> VISUALIZZA ELENCO ABITANTI MORTI NELL'ANNO SELEZIONATI </h3>";

 echo "<a href='add_persone.php'>Aggiungi nuova persona.</a><br><br>";

if (isset($_POST['cod_zona'])){
   $cod_zona = $_POST['cod_zona'];
}
//Select option per la scelta della zona
echo "<form action='rappresenta.php' method='POST'><br>";
echo   "Selezione Zona : <select name='cod_zona'>";
$result = $conn->query("SELECT * FROM zone");
$nz=$result->num_rows;
for($i=0;$i<$nz;$i++)
{
  $row = $result->fetch_array();
  echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
}
echo "</select>";
echo " <input type='submit' value='Conferma'>";
echo " </form>";

if(isset($_POST['anno_persone2']))
{
    $annata2=$_POST['anno_persone2'];
    $_SESSION["anno_persona"]=$annata2;


}
else
{
    $annata2= $_SESSION["anno_persona"];
}

$query = "SELECT ";
$query .= " p.id, p.nominativo, p.sesso, p.data_nascita,";
$query .= " c.id_moranca,c.nome nome_casa, m.nome nome_moranca,";
$query .= " m.cod_zona,  c.id_casa_moranca, c.id_osm, ";
$query .= " pc.cod_ruolo_pers_fam, rpf.descrizione,";
$query .= " p.data_inizio_val data_val, p.data_fine_val ";
$query .= " FROM persone p";
$query .= " INNER JOIN pers_casa pc ON  pc.id_pers = p.id";
$query .= " INNER JOIN casa c ON  pc.id_casa = c.id";
$query .= " INNER JOIN morance m ON  c.id_moranca = m.id";
$query .= " INNER JOIN ruolo_pers_fam rpf ON  pc.cod_ruolo_pers_fam = rpf.cod WHERE p.DATA_MORTE IS NOT  NULL and year(DATA_MORTE) = '$annata2'";
if (isset($cod_zona)){
    $query .= " AND m.cod_zona = '$cod_zona'";
     /*if (isset($_SESSION["search-bar"])){
        $query.="AND p.nominativo=".$_SESSION['search-bar'];
     }*/
}/*else if (isset($_SESSION["search-bar"])){
        $query.=" AND p.nominativo='".$_SESSION['search-bar']."'";
    }*/
$query .= " ORDER BY nominativo ASC";

$result = $conn->query($query);

//echo $query;

$nr = $result->num_rows;

if ($nr != 0)
 {
	echo "<table border>";
    echo "<tr>";  
	echo "<th>id</th>";
	echo "<th>nominativo</th>";
	echo "<th>sesso</th>";		
	echo "<th>data nascita</th>";
    echo "<th>Et&agrave;</th>";
    echo "<th>ruolo in famiglia</th>";
	echo "<th>moranca</th>";
    echo "<th>casa</th>";
    echo "<th>data val</th>";
    echo "<th>Modifica</th>";
    echo "<th>Elimina</th>";
    echo "<th>Casa</th>";
	echo "<th>Storico</th>";

 
	echo "</tr>";
      
    echo "<tr>";
    while ($row = $result->fetch_array())
    { 
		echo "<td>$row[id]</td>";
		$mystr = utf8_encode ($row['nominativo']) ;
		echo "<td>$mystr</td>";
		echo "<td>$row[sesso]</td>";
		echo "<td>$row[data_nascita]</td>";
        echo "<td>".date_diff(date_create($row['data_nascita']), date_create('today'))->y."</td>";
		echo "<td>$row[descrizione] ($row[cod_ruolo_pers_fam])</td>";
		$mystr = utf8_encode ($row['nome_moranca']) ;
		echo "<td>$mystr</td>";
		echo "<td>$row[nome_casa]</td>";
		echo "<td>$row[data_val]</td>";
     
	   echo " <form method='post' action='modifica_persona.php'>";
	   echo "<th><button class='btn center-block' name='idModifica'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";
   
	   echo " <form method='post' action='elimina_persona.php'>";
	   echo "<th><button class='btn center-block' name='idElimina'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";	
	
	   echo " <form method='post' action='mostra_casa.php'>";
       echo "<th><button class='btn center-block' name='id_persona'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i> </button> ". "</th></form>";
    
	   echo " <form method='post' action='persona_storico.php'>";
       echo "<th><button class='btn center-block' name='id_persona'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i> </button> ". "</th></form>";
	   echo "</tr>";
    } 
	  echo "</table>";
	}
	else
        echo " Nessuna persona &egrave; presente nel database.";


// Se le pagine totali sono pi? di 1...
// stampo i link per andare avanti e indietro tra le diverse pagine!
  
  echo "<br> Numero abitanti: $all_rows<br>";

  $result->free();
  $conn->close();	
  
 ?>

<form action="statistiche.php"> <input type="submit" value=TORNA> </form>
 </body>
</html>