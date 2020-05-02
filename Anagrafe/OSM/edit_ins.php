<?php
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util1;
require_once $util2;
setup();


print '<!DOCTYPE html><html>  <head>';
header('Content-Type: text/html; charset=utf-8');
print '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />';   
print '<link rel="stylesheet" type="text/css" href="styleOSM.css">';
?>

<?php
print '</head> <body>';

include "../db/db_util.php";		// funzioni di utilità sul DB

$ID = "";
if( isset($_GET["ID"]) )
	{
     $ID = htmlspecialchars($_GET["ID"]);
    }
$lon = "";
if( isset($_GET["lon"]) )
	{
     $lon = htmlspecialchars($_GET["lon"]);
	}
$lat = "";
if( isset($_GET["lat"]) )
 	{
     $lat = htmlspecialchars($_GET["lat"]);
    }


$points = file_get_contents('points.geojson');
$pointsarray = json_decode($points, true);

$settings = file_get_contents('settings.json');
$settingsarray = json_decode($settings, true);

if( isset($POST["casa"]) )			// se arriva dal POST è una nuova associazione tra id_osm e casa
	{
	 $ID = "new_assoc";
     $id_casa = htmlspecialchars($_POST["casa"]);
//	 echo "ID = ". $ID;
//	 echo "id_casa = ". $id_casa;
    }

if ($ID != "" && $lon != "" && $lat != "") {

    $i = 0;
    foreach ($pointsarray["features"] as $key => $item) {
        if ($item["properties"]["name"] == $ID){
            break;
        }
        $i++;
    }

    if ($i >= count($pointsarray["features"])) {
        $i = count($pointsarray["features"])-1;
    }

    print "<h3> Modifica casa id=".$ID."</h3>";

    print '<form action="edit_ins.php" method="post">';

    print '<input type="hidden" name="ID" value="'.$ID.'">';

    $mytag = $pointsarray["features"][$i]["properties"]["tag"];


	print '<b>zona:</b><br>&nbsp <input type="text" class="onlyread" name="tag" value="'.$mytag.'" readonly><br>';


    print '<b>Latitudine:</b><br>';
    print '&nbsp;<input type="text" class="onlyread" name="lat" value="'.$lat.'" readonly><br>';

    print '<b>Longitudine:</b><br>';
	print '&nbsp;<input type="text" class="onlyread" name="lon" value="'.$lon.'" readonly><br>';

    $n = 0;
    foreach ($pointsarray["features"][$i]["properties"]["description"] as $key => $item) {
        print '<b>'.$key.':</b><br>';
        if ($ID == "new") $item = "";		// nuova casa da identificare
		switch ($key) 
		 {
			case "id OSM":	//D0
			 $id_osm = $item;
			 if ($ID == "new")
			   {
				$result = get_osm_id($lat, $lon, $id_osm); // a partire da lat e lon, recupero id_osm
			    if ($result<0)
                  echo "errore accesso a OpenStreetMap";
			   }
			 else
				  $id_osm = $item;
			 print '#<input type="number" name="D'.$n.'" value="'.$id_osm.'" min=0 required>* obbl.<br>';
			 break;

			case "Nome Casa":	//D1
   	         $nome_casa = $item;
             if ($ID == "new")		// nuovo punto
			   {
//				echo "nuovo punto<br>";

				// cerco le case che non sono ancora state associate tramite id_osm e le presento per la selezione
                $query = "SELECT m.nome as nome_moranca, z.nome as zona, c.id as id_casa, c.nome as nome_casa,";
                $query .= "p.nominativo as capo_famiglia";
                $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
                $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
                $query .= "LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
                $query .=" AND pc.cod_ruolo_pers_fam = 'CF'";
                $query .=" LEFT JOIN persone p ON p.id = pc.id_pers";
                $query .= " WHERE c.DATA_FINE_VAL is null";
                $query .= " AND !(c.id_osm IS NOT NULL AND  c.id_osm != 0)";
                $query .= " ORDER BY c.id ASC";
                $result = $conn->query($query);  

        //       echo $query;
               /*
               *** form  per la scelta della casa
               */ 
   //            echo "<form action='edit_ins.php'  method='POST'><br>";
               echo   "casa: <select name='casa' required>";
               $nr=$result->num_rows;
               echo "<option></option><br>";
               for($c=0;$c<$nr;$c++)
               {
                 $row = $result->fetch_array();
				 $id_casa = $row['id_casa'];
				 $zona = $row['zona'];
				 $nome_casa = $row['nome_casa'];
                 $myCapoFam = utf8_encode ($row['capo_famiglia']) ;
                 $myMoranca = utf8_encode ($row['nome_moranca']) ;
                 if ($myCapoFam=="")
               		  $myCapoFam = "Non Esiste";
                 echo "<option value=".$row['id_casa'].">casa (id:". $row['id_casa'].")-nome:".$nome_casa." (capo famiglia:".$myCapoFam. ") - moranca:".$myMoranca."  </option>";  					 
                }
				echo "<input type='submit' value='Scegli'>";
			    echo "</select>";
               }
			 else
			  {			// modifica punto
//			       echo "modifica punto";
//				   echo "item=". $item;
				   print '&nbsp;<input type="text" name="D'.$n.'" value="'.$item.'" required>* obbl.<br>';
			  }
			 break;
		
		  default:
			 print '&nbsp;<input type="text" class="onlyread" name="D'.$n.'" value="'.$item.'" readonly><br>';
			 break;
         }
       $n++;
    }

    $verified = $pointsarray["features"][$i]["properties"]["verified"];
    if ($ID == "new") $verified = "";
       print '<b>Ultima modifica:</b><br>';

    print '<input type="text" class="onlyread" name="verified" value="'.$verified.'" readonly="readonly"><br>';
	
 //   if ($ID != "new") print '<input type="checkbox" name="delete" value="delete"> Cancella<br>';

    print ' <br><input type="submit" class = "button" value="Salva">';
    print '</form>';
}

if( isset($_POST["ID"]) ){
//if ($_POST["ID"] != ""){
    $ID = $_POST["ID"];
    $lon = $_POST["lon"];
    $lat = $_POST["lat"];
    $tag = $_POST["tag"];
    $verified = $_POST["verified"];
    $verified = date("d/m/Y");

//   echo "POST: ID = ". $ID;
   if (isset($_POST['casa']))
	 {
//	   echo "POST: id_casa = ". $_POST['casa'];
       $id_casa = $_POST['casa'];
     }
	else
		$id_casa = $ID;
    $i = 0;
    foreach ($pointsarray["features"] as $key => $item) {
        if ($item["properties"]["name"] == $ID){
            break;
        }
        $i++;
    }

    if( isset($_POST["delete"]) )
 	{
    if ($_POST['delete'] == 'delete' && $ID != "new") {
        print "<b>Cancellazione...".$ID."</b><br>";
        $new = json_decode('{"type":"FeatureCollection","features":[]}', true);
        foreach ($pointsarray["features"] as $item) {
            if($item["properties"]["name"] != $ID) {
                $new["features"][] = $item;
            }
            $i++;
        }
        print "<b>Da cancellare su DB casa id = ". $ID;

        $geojson = json_encode($new);
        file_put_contents('points.geojson', $geojson);

        print '<h3>Cancellazione effettuata su file json</h3>';
		$ret = cancella_casa($ID);
		if ($ret == -1)
		   print "<h3>Errore in cancella_casa()  casa id=". $ID . "</h3>";
					//header("Location: mod_db.php?ID=".$ID);
		else
		   print "<h3>Cancellazione casa effettuato: casa id=". $ID . "</h3>";
      }

    } else {
        $INS = 0;
//		echo "ID=". $ID;
        if ($ID == "new")
		{
			$INS= 1;	// da fare inserimento sul file geojson (ma non sul DB)
            $ID = strval($i+1);
            $pointsarray["features"][$i] = $pointsarray["features"][0];
        }
        
//		echo "inserisco in posizione i=".$i;
        $pointsarray["features"][$i]["properties"]["name"] = $id_casa;
        $pointsarray["features"][$i]["geometry"]["coordinates"][0] = doubleval($lon);
        $pointsarray["features"][$i]["geometry"]["coordinates"][1] = doubleval($lat);
        $pointsarray["features"][$i]["properties"]["tag"] = $tag;
        $pointsarray["features"][$i]["properties"]["verified"] = $verified;

	    $nome_casa = "";
        $nome_moranca = "";
        $nome_zona = "";
        $capo_famiglia = "";
		$num_persone = 0;
		$ret = casa_get_info($id_casa,$nome_casa, $nome_moranca, $id_osm, $nome_zona, $capo_famiglia,$num_persone);
        if ($INS ==1)
		 {
		  $pointsarray["features"][$i]["properties"]["description"]["id OSM"] = $_POST['D0'];
		  $pointsarray["features"][$i]["properties"]["description"]["Nome Casa"] = $nome_casa;
		  $pointsarray["features"][$i]["properties"]["description"]["Moranca"] = $nome_moranca;
		  $pointsarray["features"][$i]["properties"]["description"]["Capo Famiglia"] = $capo_famiglia;
		  $pointsarray["features"][$i]["properties"]["description"]["Numero Persone"] = $num_persone;
         }
		else
         {
		   $pointsarray["features"][$i]["properties"]["description"]['id OSM'] = $_POST['D0'];
//		   echo "id OSM=". $_POST['D0'];
	       $pointsarray["features"][$i]["properties"]["description"]['Nome Casa'] = $_POST['D1'];
//		   echo "casa=". $_POST['D1'];
         }
        /* per ora non inserisco le descrizioni.
        $n = 0;
        foreach ($pointsarray["features"][$i]["properties"]["description"] as $key => $item) {
            $pointsarray["features"][$i]["properties"]["description"][$key] = $_POST["D".$n];
            $n++;
		
        }
         */

  //      $geojson = json_encode($pointsarray);
		$geojson = json_encode($pointsarray, JSON_PRETTY_PRINT);

        file_put_contents('points.geojson', $geojson);

        if ($INS == 1)  // inserita nuova associazione tra id_osm e casa
			{         
			  $ret = nuova_assoc_casa($id_casa);
			  if ($ret == -1)
				 print "<h3>Errore in inserisci_casa()  casa id=". $id_casa . "</h3>";
					//header("Location: mod_db.php?ID=".$ID);
			  else
		        print "<h3>Casa id=". $id_casa . " associata a id_OSM  correttamente</h3>";
		    }
        else
			{   
			  $ret = modifica_casa($ID);		// da modificare casa su DB
			  if ($ret == -1)
				 print "<h3>Errore in Modifica  casa id=". $id_casa . "</h3>";
			  else
		         print "<h3>Modifica casa id=". $id_casa . "effettuata correttamente</h3>";

		    }
        //print $geojson;
    }

}

print '  </body> </html>';
?>
