<?php
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
isLogged("gestore");
?>
<html>
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<?php //stampaIntestazione(); ?>
<body>
<?php //stampaNavbar(); ?>
 <?php
 $util2 = $config_path .'/../db/db_conn.php';
 require_once $util2;
?>
<?php stampaIntestazione(); ?>
<?php stampaNavbar(); ?>
    <div class="search-box">
        <input type="text" autocomplete="off" placeholder="Ricerca casa..." />
        <div class="result"></div>
    </div>
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
$query = "SELECT count(id) as cont FROM casa";
$result = $conn->query($query);
$row = $result->fetch_array();
$all_rows= $row['cont'];

    
//  definisco il numero totale di pagine
$all_pages = ceil($all_rows / $x_pag);

// Calcolo da quale record iniziare
$first = ($pag - 1) * $x_pag;
$id_persona=$_POST["id_persona"];

   $query =   "SELECT c.id id_casa FROM casa c ";
    $query .= "INNER JOIN pers_casa pc  ON c.id = pc.id_casa ";
   $query .=  "INNER JOIN persona p  ON  p.id = pc.id_pers ";
  
   $result = $conn->query($quer1);
   $row = $result->fetch_array();

   $id_casa= $row['id_casa'];
   $result->free();

   $query = "SELECT c.id, c.nome,";
   $query .= " z.nome zona, c.id_moranca, m.nome nome_moranca, c.nome, p.id id_pers, p.nominativo, c.id_osm, ";
   $query .= " c.data_inizio_val, c.data_fine_val";
   $query .= " FROM casa c INNER JOIN morance m  ON  c.id_moranca = m.id ";
   $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
   $query .= " INNER JOIN pers_casa pc  ON  pc.id_casa = c.id ";
   $query .= " INNER JOIN persone p  ON  pc.id_pers = p.id ";
   $query .= " AND c.id = $id_casa";
 
   $result = $conn->query($query);
   
   echo $query;

    echo "<h2> Villaggio di NTchangue</h2>";
    echo "<h3> Elenco Case</h3>";

	if ($result->num_rows !=0)
	{     
		echo "<table border>";
		echo "<tr>";
		echo "<th>id</th>";
		echo "<th>nome</th>";
		echo "<th>zona</th>";
		echo "<th>id moranca</th>";
		echo "<th>moran&ccedil;a</th>";
		echo "<th>id_capo_famiglia</th>";
		echo "<th>capo_famiglia</th>";
		echo "<th>id su OSM</th>";
        echo "<th>data inizio val</th>";
        echo "<th>data fine val</th>";
        echo "<th>Modifica</th>";
        echo "<th>Elimina</th>";
        echo "<th>Persone </th>";

			echo "</tr>";

	    while ($row = $result->fetch_array())
		 {
			echo "<tr>";
			echo "<td>$row[id]</td>";
			echo "<td>$row[nome]</td>";
			echo "<td>$row[zona]</td>";
			echo "<td>$row[id_moranca]</td>";
		    echo "<td>$row[nome_moranca]</td>";
			echo "<td>$row[id_pers]</th>";
			echo "<td>$row[nominativo]</th>";
            $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
            if ($row['id_osm'] != null)
             { 
			  echo "<td>$row[id_osm]&nbsp;<a href=$osm_link target=new>vai alla mappa&nbsp;<IMG SRC=../css/osm.png WIDTH=20 HEIGHT=20 BORDER=0></a></td>"; 
		     }
		    else
             { 
              echo "<td>$row[id_osm]&nbsp;</td>";
             }
			echo "<td>$row[data_inizio_val]</td>";
			echo "<td>$row[data_fine_val]</td>";
            echo " <form method='post' action='modifica_casa.php'>";
	echo "<th><button class='btn center-block' name='idModifica'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";
            echo " <form method='post' action='elimina_casa.php'>";
	echo "<th><button class='btn center-block' name='idElimina'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";
            
            
             echo " <form method='post' action='../case/mostra_persone.php'>";
    echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i></button> ". "</th></form>";
	echo "</tr>";
            
            
		 }
		 echo "</table>";
	}
	else
		echo " Nessuna casa &egrave; presente nel database.";

  $result->free();
  $conn->close();	
 ?>
 <br>
  <a href="aggiungi_casa.php">
  Aggiungi nuova casa.
  </a><br>
  

 
 </body>
</html>