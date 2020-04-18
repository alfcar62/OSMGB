<?php
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
isLogged("utente");
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
$id_moranca=$_POST["id_moranca"];

 $query = "SELECT  nome FROM morance WHERE id = ". $id_moranca;
 $result = $conn->query($query);
 $row = $result->fetch_array();
 $nome_moranca = $row['nome'];
 $result->free();

  $query = "SELECT c.id, c.nome,";
  $query .= " z.nome zona, c.id_moranca, m.nome nome_moranca, c.nome, p.id id_pers, p.nominativo, m.id_osm, ";
  $query .= " m.data_inizio_val, m.data_fine_val";
  $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
  $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
  $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
  $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
  $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
  $query .= " WHERE m.id = $id_moranca ";
  $query .= " ORDER BY c.id ASC";
  $result = $conn->query($query);

//echo $query;

    echo "<h2> Villaggio di NTchangue</h2>";
	echo "<h3> ELENCO CASE DELLA MORANCA: $nome_moranca (id=$id_moranca)  </h3>";

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
		echo "<th>n.abitanti</th>";
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
			$mystr = utf8_encode ($row['nome_moranca']) ;
		    echo "<td>$mystr</td>";
			echo "<td>$row[id_pers]</th>";
			$mystr = utf8_encode ($row['nominativo']) ;
			echo "<td>$mystr</th>";

			$query2="SELECT COUNT(pers_casa.ID_PERS) as persone from pers_casa WHERE ID_CASA='$row[id]'";
            $result2 = $conn->query($query2);
            $row2 = $result2->fetch_array();
            echo "<td>$row2[persone]</th>";

            $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
            if (($row['id_osm'] != null) && ($row['id_osm'] != 0))
             { 
			  echo "<td>$row[id_osm]&nbsp;<a href=$osm_link target=new>vai alla mappa&nbsp;<IMG SRC=../css/osm.png WIDTH=20 HEIGHT=20 BORDER=0></a></td>"; 
		     }
		    else
             { 
              echo "<td>&nbsp;</td>";
             }
			echo "<td>$row[data_inizio_val]</td>";
			echo "<td>$row[data_fine_val]</td>";
            echo " <form method='post' action='../case/mod_casa.php'>";
	echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";
            echo " <form method='post' action='../case/del_casa.php'>";
	echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";
                   
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
 </body>
</html>