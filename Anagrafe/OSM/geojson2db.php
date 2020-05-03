<?php
print '<!DOCTYPE html><html>  <head>';
header('Content-Type: text/html; charset=utf-8');
print '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';
print '</head> <body>';

/**
*** PHP GeoJSON Constructor, adpated from https://github.com/bmcbride/PHP-Database-GeoJSON
***
*** carica i  dati su DB a partire dal file in formato geojson
**/
$err = 0;

header("Content-Type: application/json; charset=UTF-8");
include ("db_conn.php");

// lettura dal file geojson
$points = file_get_contents('points.geojson');
$pointsarray = json_decode($points, true);

$i = 0;
foreach ($pointsarray["features"] as $key => $item)
 {	 
	 $idc = $pointsarray["features"][$i]["properties"]["name"];
	 print '<b>id casa:'.$idc.':</b><br>';
	 
	 $tag = $pointsarray["features"][$i]["properties"]["tag"];
	 print '<b> tag:'.$tag.':</b><br>';

	 $data_val = $pointsarray["features"][$i]["properties"]["verified"];
	 print '<b> data validazione:'.$data_val.':</b><br>';
	
	 $nome = $pointsarray["features"][$i]["properties"]["description"]["Casa"];
	 print '<b>nome casa:'.$nome.':</b><br>';
	 $moranca = $pointsarray["features"][$i]["properties"]["description"]["Moranca"];
	 print '<b> moranca:'.$moranca.':</b><br>';
     $capof = $pointsarray["features"][$i]["properties"]["description"]["Capo Famiglia"];
	 print '<b> capo famiglia:'.$capof.':</b><br>';
	 $numper = $pointsarray["features"][$i]["properties"]["description"]["Numero persone"];
	 print '<b> numero persone:'.$numper.':</b><br>';
	 $lon = $pointsarray["features"][$i]["geometry"]["coordinates"][0];
	 print '<b> longitude:'.$lon.':</b><br>';
	 $lat = $pointsarray["features"][$i]["geometry"]["coordinates"][1];
	 print '<b> latitude:'.$lat.':</b><br>';
	

	$query = "INSERT INTO CASA ( id, nome, moranca, capo_famiglia, num_persone, latitude, longitude, tag, data_val) ";
	$query .= " VALUES (";
	$query .= $idc . ", ";
	$query .= "'". $nome . "', ";
	$query .= "'". $moranca . "', ";
	$query .= "'". $capof . "', ";
	$query .=  $numper . ", ";
	$query .=  $lat . ", ";
	$query .=  $lon . ", ";
	$query .=  "'".$tag . "', ";
	$query .=  " STR_TO_DATE('". $data_val ."', '%d/%m/%Y'))"; 
	echo "<br> query: ".$query . "<br><br>";

	$result = mysqli_query($conn, $query);

	if (!$result)
	  {
	    echo 'Errore istruzione SQL\n';
		echo  $query;
		$err = 1;
	  }
	$i++;
 }

// close connection 
mysqli_close($conn);
if ($err == 0)
 echo "<br> dati inseriti correttamente su DB\n";
else
 echo "<br> errore inserimento su DB\n";
header('Content-Type: text/html; charset=utf-8');

?>
  </body>
</html>
