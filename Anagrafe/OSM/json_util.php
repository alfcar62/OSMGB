<?php
/*
*** inserisci_casa su file json
*** input: id casa
*** return: -1 errore
***         0 OK
*/


function inserisci_casa_json($newid, $nome, $moranca, $capof, $nump, $lat, $lon, $tag)
{
 $points = file_get_contents('points.geojson');
 $pointsarray = json_decode($points, true);
 $ID = $newid;

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

echo "i=". $i+1;
$pointsarray["features"][$i] = $pointsarray["features"][0];

$pointsarray["features"][$i]["properties"]["name"] = $ID;
$pointsarray["features"][$i]["geometry"]["coordinates"][0] = doubleval($lon);
$pointsarray["features"][$i]["geometry"]["coordinates"][1] = doubleval($lat);
$pointsarray["features"][$i]["properties"]["tag"] = $tag;
//$pointsarray["features"][$i]["properties"]["verified"] = $verified;



 $pointsarray["features"][$i]["properties"]["description"]["nome"] = $nome;
 $pointsarray["features"][$i]["properties"]["description"]["moranca"] = $moranca;
 $pointsarray["features"][$i]["properties"]["description"]["capof"] = $capof;
 $pointsarray["features"][$i]["properties"]["description"]["nump"] = $nump;

 $geojson = json_encode($pointsarray);
 file_put_contents('points.geojson', $geojson);
 }
?>