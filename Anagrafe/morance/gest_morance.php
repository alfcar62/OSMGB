<?php
/*
*** Gest_morance.php: Gestione delle Morance
*** 
*** 11/3/2020: A.Carlone: migliorata gestione zone e ordinamento su id e nome moranca
*** 27/02/20 : Gobbi: Implementazione della gestione multilingue
*** 2/2/2020: A. Carlone: prima implementazione
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
unsetPag(basename(__FILE__));
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
if(isset($_SESSION['errore']) && $_SESSION['errore']=='error'){echo "<script>alert('Esistono case nella moranca: impossibile cancellare')</script>";
                                                                         }
$_SESSION['errore']=null;

?>
<html>
 <link rel="stylesheet" type="text/css" href="../css/style.css">
 <link rel="stylesheet" type="text/css" href="gest_morance_temp_css.css">

 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("cerca_moranca.php", {term: inputVal}).done(function(data){
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
<body>
<?php stampaNavbar(); ?>
<div class="search-box">
<input type="text" autocomplete="off" placeholder="Ricerca ..." />
	<div class="result"></div>
</div>
<div id="lb-back">
       <div id="lb-img"></div>
</div>
<!-- Modal:div che compare quando si clicca sull'immagine -->
<div id="myModal" class="modal">

<!-- The Close Button -->
 <span class="close">&times;</span>

 <!-- Modal Content (The Image) -->
 <img class="modal-content" id="img01">
</div>
<?php 

// modificato per la gestione corretta della paginazione (A.C. 10/3/2020)
// se $_POST['cod_zona'] valorizzato --> arriva  dall'action form
// se $_SESSION  valorizzato --> arriva  dal $SERVER[PHP_SELF]
if (isset($_POST['cod_zona']))
 {
  $cod_zona = $_POST['cod_zona']; 
  $_SESSION['cod_zona'] = $cod_zona;
 }  
else 
 {
   if( isset($_SESSION['cod_zona']) &&  ($_SESSION['cod_zona'] != 'tutte'))		
	   $cod_zona =  $_SESSION['cod_zona'];
 } 

//echo " cod_zona = ". $cod_zona;
//echo " SESSION(cod_zona) = ".$_SESSION['cod_zona'];

// Creo una variabile dove imposto il numero di record 
// da mostrare in ogni pagina
$x_pag = 10;



$pag=Paginazione("pag_m");	// Recupero il  numero di pagina corrente
    
// Controllo se $pag ? valorizzato e se ? numerico
// ...in caso contrario gli assegno valore 1
if (!$pag || !is_numeric($pag))
    $pag = 1; //prima volta che entro

// Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella 
$query = "SELECT count(id) as cont FROM morance where DATA_FINE_VAL IS null";
if (isset($cod_zona) && $cod_zona != 'tutte')
  $query .= " AND cod_zona ='". $cod_zona ."'";

//echo $query;
$result = $conn->query($query);
$row = $result->fetch_array();
$all_rows= $row['cont'];
  
//  definisco il numero totale di pagine
$all_pages = ceil($all_rows / $x_pag);


//echo " all_pages=". $all_pages;
// Calcolo da quale record iniziare
$first = ($pag - 1) * $x_pag;

echo "<h2>Villaggio di NTchangue: Elenco moran&ccedil;e</h2>";

echo "<a href='ins_moranca.php'>Inserisci una nuova  moran&ccedil;a</a><br><br>";//Aggiungi una nuova moranca

echo "<a href='export_moranca.php'>Export su Excel</a><br><br>";//Export su excel

echo "<a href='vis_sto_tot_morance.php'>";
        echo "Storia delle morance </a><br><br>";


//Select option per la scelta della zona
echo "<form action='gest_morance.php' method='POST'><br>";
echo   $jsonObj->{$lang."Morance"}[3].": <select name='cod_zona'>";
$result = $conn->query("SELECT * FROM zone");
$nz=$result->num_rows;

echo "<option value='tutte'>  tutte </option>";
for($i=0;$i<$nz;$i++)
{
 $row = $result->fetch_array();

 if(isset($cod_zona) && $cod_zona == $row["COD"])
			echo "<option value='".$row["COD"]."' selected>". $row["NOME"]." </option>";
		else
			echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
}
echo "</select>";
echo " <input type='submit' class='button' value='".$jsonObj->{$lang."Morance"}[4]."'>";//Conferma
echo " </form>";


// ordinamento su campi (11/3/2020) A.C.
if (!isset($_POST['ord'])) 
 {
  $campo = 'id';  
  $ord = 'ASC';	// ordinamento ascendente
 }
else
 { 
   $campo = $_POST['campo'];
   if ($_POST['ord'] == 'ASC')
	   $ord = 'DESC';
   else
       $ord = 'ASC';
 }

$query = "SELECT ";
$query .= " m.id, m.nome, z.nome zona,m.id_mor_zona,m.id_osm,";
$query .= " m.data_inizio_val, m.data_fine_val";
$query .= " FROM morance m, zone z ";
$query .= " WHERE m.data_fine_val IS NULL";
$query .= " AND m.cod_zona = z.cod";
if (isset($cod_zona) && ($cod_zona !='tutte'))
    $query .= " AND m.cod_zona = '$cod_zona'";
$query .= " ORDER BY $campo " . $ord ;
$query .= " LIMIT $first, $x_pag";


//echo $query;
$result = $conn->query($query);
$numero=$result->num_rows;
if ($result->num_rows !=0)
 {
  echo "<table border>";
  echo "<tr>";


  //foto

  echo "<th>Foto</th>";
  
  //id (con possibilità di ordinamento)
   echo " <form method='post' action='gest_morance.php'>";
   echo "<input type='hidden' name='ord' value= $ord>";
   echo "<th> id <button class='btn center-block'  name='campo'  value='id' type='submit'><i class='fa fa-sort' title ='ordina'></i>  </button> </th></form>";

  //nome Moranca  (con possibilità di ordinamento)

  echo " <form method='post' action='gest_morance.php'>";
  echo "<input type='hidden' name='ord' value= $ord>";
  echo "<th>".$jsonObj->{$lang."Morance"}[5]."<button class='btn center-block'  name='campo'  value='m.nome' type='submit'><i class='fa fa-sort' title ='ordina'></i> </button> </th></form>";

  echo "<th>".$jsonObj->{$lang."Morance"}[6]."</th>";//Zona
  echo "<th>".$jsonObj->{$lang."Morance"}[7]."</th>";//progr nella zona
  echo "<th> su OpenStreetMap";
  echo "<th>data inizio val";//data_val
  echo "<th>".$jsonObj->{$lang."Morance"}[9]."</th>";//Modifica
  echo "<th>".$jsonObj->{$lang."Morance"}[10]."</th>";//Elimina
  echo "<th>".$jsonObj->{$lang."Morance"}[11]."</th>";//Case
  echo "<th>Storico";//Storico

  echo "</tr>";

  while ($row = $result->fetch_array())
   {
		    $mystr = utf8_encode ($row['nome']) ;

	        echo "<tr>";           
                $immagine=glob('immagini/'.$row['id'].'.*');
                if($immagine != null)
                    echo "<td><div ><img src='$immagine[0]' class='modal_image' style='display: block; margin-left: auto; margin-right: auto;width:35px;height:30px'  ></div></td> ";
                else{
                    echo '<td><i class="fa fa-image"></i></td>';
                }
			echo "<td>$row[id]</td>";
			echo "<td>$mystr</td>";
		    echo "<td>$row[zona]</td>";
			echo "<td>$row[id_mor_zona]</td>";

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

            echo " <form method='post' action='mod_moranca.php'>";
            echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";

            echo " <form method='post' action='del_moranca.php'>";
            echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";

            echo " <form method='post' action='mostra_case.php'>";
            echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i> </button> ". "</th></form>"; 

			echo " <form method='post' action='vis_moranca_sto.php'>";
            echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i> </button> ". "</th></form>";    
            echo "</tr>";
    }      
   echo "</table>";      
 }
 else
   echo " Nessuna moran&ccedil; &egrave; presente nel database.";

// Se le pagine totali sono pi? di 1...
// stampo i link per andare avanti e indietro tra le diverse pagine!
  echo "<br>".$jsonObj->{$lang."Morance"}[13].": $all_rows<br>";//Numero di morance

  if ($all_pages > 1){
  if ($pag > 1){
    echo "<br><a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag - 1) . "\">";
    echo $jsonObj->{$lang."Morance"}[15]."</a>&nbsp;<br><br>";//Pagina indietro
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
    echo $jsonObj->{$lang."Morance"}[14]."<br></a>";//Pagina avanti
   } 
}

  $result->free();
  $conn->close();	
 ?>
 </body>
</html>
