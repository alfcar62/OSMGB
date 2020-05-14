
<?php
print '<!DOCTYPE html><html>  <head>';
header('Content-Type: text/html; charset=utf-8');
print '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';
print '</head> <body>';


/*
*** ripristina(): ripristina una situazione iniziale sul DB e sul file geojson
*** 1.salva il file points.geojson in points.geojson.old
*** copia i dati dal DB e li mette sul  file points.geojson 
*** return: -1 errore
***         0 OK
*/

 include ("db_conn.php");
 
 $timestamp = strtotime("+1 day");
 $date =  date('d/m/Y', $timestamp);
 $file = "../OSM/points.geojson";
 echo $file;

 $newfile = $file . "_old";
 echo $newfile;

if (!copy($file, $newfile)) {
    echo "failed to copy $file...\n";
}
 
 $query = "DELETE  FROM casa ";

 echo "<br> query: ". $query . "<br><br>";

 $result = mysqli_query($conn, $query);

 if (!$result)
	  {
	    echo 'Errore istruzione SQL\n';
		echo  $query;
		exit;
	  }
   
echo "<br> numero righe cancellate su DB=".  mysqli_affected_rows($conn). "<br>";

echo "<br>  case  cancellate correttamente su DB\n";

$idc=1;
$nome= "CASA1";
$moranca = "MORANCA1";
$capof = "Prova";
$numper = 10;
$lat =12.005265 ;
$lon = -15.515260;
$tag = "first category";
$data_val = "2019-11-23"; 
	
	
$query = "INSERT INTO casa ";
$query .= "( id, nome, moranca, capo_famiglia, num_persone, latitude,longitude, tag, data_val) ";
$query .= " VALUES (";
$query .= $idc . ",";
$query .= "'" . $nome . "',";
$query .= "'" . $moranca . "',";
$query .= "'" . $capof . "',";
$query .=  $numper . ",";
$query .=  $lat . ",";
$query .=  $lon . ",";
$query .= "'" . $tag . "',";
$query .= "'" . $data_val . "'";
$query .= ")";

 echo "<br> query: ".$query . "<br><br>";

 $result = mysqli_query($conn, $query);

 if (!$result)
	  {
	    echo 'Errore istruzione SQL\n';
		echo  $query;
		exit;
	  }
  
  echo "<br> numero righe inserite su DB=".  mysqli_affected_rows($conn). "<br>";
  echo "<br> situazione iniziale ripristinata su DB <br>";
 // close connection 
mysqli_close($conn);

include("db2geojson.php");
header('Content-Type: text/html; charset=utf-8');

echo "<br> situazione iniziale ripristinata su geojson <br>";
?>
<br>
<br>
<a href="../util.html">
Torna.
</a>
<br>
<a href="../anagrafe.html">
 Home.
</a>
  </body>
</html>

