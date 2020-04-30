<?php
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
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

    print '<form action="edit.php" method="post">';

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
        if ($ID == "new") $item = "";
		switch ($key) 
		 {
           case "Nome Casa":
			 print '&nbsp;<input type="text" name="D'.$n.'" value="'.$item.'" required>* obbl.<br>';
			 break;
			case "id OSM":
			 print '#<input type="number" name="D'.$n.'" value="'.$item.'" min=0 required>* obbl.<br>';
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
        if ($ID == "new") {
			$INS= 1;	// da fare inserimento 
            $ID = strval($i+1);
            $pointsarray["features"][$i] = $pointsarray["features"][0];
        }

        $pointsarray["features"][$i]["properties"]["name"] = $ID;
        $pointsarray["features"][$i]["geometry"]["coordinates"][0] = doubleval($lon);
        $pointsarray["features"][$i]["geometry"]["coordinates"][1] = doubleval($lat);
        $pointsarray["features"][$i]["properties"]["tag"] = $tag;
        $pointsarray["features"][$i]["properties"]["verified"] = $verified;

        $n = 0;
        foreach ($pointsarray["features"][$i]["properties"]["description"] as $key => $item) {
            $pointsarray["features"][$i]["properties"]["description"][$key] = $_POST["D".$n];
            $n++;
        }
          

        $geojson = json_encode($pointsarray);
        file_put_contents('points.geojson', $geojson);

        if ($INS == 1)  // da inserire casa su DB
			{         
			  $ret = inserisci_casa($ID);
			  if ($ret == -1)
				 print "<h3>Errore in inserisci_casa()  casa id=". $ID . "</h3>";
					//header("Location: mod_db.php?ID=".$ID);
			  else
		        print "<h3>Inserimento casa effettuato: casa id=". $ID . "</h3>";
		    }
        else
			{   
			  $ret = modifica_casa($ID);		// da modificare casa su DB
			  if ($ret == -1)
				 print "<h3>Errore in Modifica  casa id=". $ID . "</h3>";
	//		  else

//		           print "<h3>Modifica effettuata casa id=". $ID . "</h3>";

		    }
        //print $geojson;
    }

}

print '  </body> </html>';
?>
