<?php
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
isLogged();
?>

<html>
<?php stampaIntestazione(); ?>
<body>
<?php stampaNavbar(); 
?>
<br>
<a href='statistiche_det.php'> Dettaglio Statistiche <IMG SRC="../img/inserisci2.png"></a>
&nbsp;&nbsp;
<a href='statistiche_zona.php'>Statistiche per zona <i class="fa fa-pie-chart" aria-hidden="true"></i></a>
<br>

<?php
$util = $config_path .'/../db/db_conn.php';
require $util;
?>

<?php
$oraoggi=date("Y/m/d");
$zona=$_GET["zona_richiesta"];

//media età delle persone 
$query = " select avg(DATEDIFF(CURDATE(),data_nascita)) as etamedia from persone";
$query .= " inner join pers_casa on pers_casa.ID_PERS=persone.ID ";
$query .= " inner join casa on pers_casa.ID_casa=casa.ID";
$query .= " inner join morance on casa.ID_moranca=morance.ID";
$query .= " inner join zone on morance.cod_zona=zone.COD";
$query .= " where  zone.NOME='$zona'";

$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
 $row = $result->fetch_array();
 //echo " media eta delle persone: ";
 $etamedia = floor($row['etamedia']/365);
}



//persone in totale
$query = "SELECT *  from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona' ";
$result=$conn->query($query);
//echo  $query;

echo $conn->error.".";
if($result)
{
  $numero_persone=$result->num_rows;
}

// 7300 giorni = 20 anni = 365 * 20
//persone con età minore  di 20 anni
$query = "SELECT count(persone.id) as indice from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona'  and  DATEDIFF('$oraoggi',data_nascita)>=0 and DATEDIFF('$oraoggi',data_nascita)<= 7300";
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone superiori a 20 anni ";
$minori20=$row ["indice"];

//echo $row ["count(id)"];
}

//persone con età tra i 20 e i 40
$query = "SELECT count(persone.id) as indice from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona'  and  DATEDIFF('$oraoggi',data_nascita)>7300 and DATEDIFF('$oraoggi',data_nascita)<= 14600";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone con età tra 20 e 40 anni ";
$persone20_40=$row ["indice"];
//echo $row ["count(id)"];
}
                     


//persone tra 40 e 60
$query = "SELECT count(persone.id) as indice from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona'  and  DATEDIFF('$oraoggi',data_nascita)>14600 and DATEDIFF('$oraoggi',data_nascita)<= 21900";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
$persone_40_60=$row ["indice"];

}

//persone con età maggiore di 60 anni
$query = "SELECT count(persone.id) as indice from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona'  and  DATEDIFF('$oraoggi',data_nascita)>21900";
$result=$conn->query($query);
//echo  $query;
//echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone superiori a 60 anni ";
$maggiori60=$row ["indice"];

}
$sprovvisti=$numero_persone-($minori20+$persone20_40+$persone_40_60+$maggiori60);
/*echo "min20 ".$minori20;
echo  "min40 ".$persone20_40;
echo "min60 ".$persone_40_60;
echo "max60".$maggiori60;
echo "sapere ".$sprovvisti;*/



$anno_corrente=date("yy");
//echo $anno_corrente;









?>

<script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

<div position="absolute"  align="center">
<div id="chartContainer1"   style="width: 70%;  height: 500px;  display: inline-block;"></div> 

</div>
<div style=' text-align: center;'>
<?php
echo "</h2>";
echo "</br></br>Età media : ".(ceil($etamedia*10))/10;
echo "</h2>";

echo "</br>";
echo "<form action='' method='GET' >";

echo "<select name='zona_richiesta'>";
echo "<option value='nord'>nord</option>";
echo "<option value='ovest'>ovest</option>";
echo "<option value='sud'>sud</option>
</select>
<input type='submit' name='invia'>
</form>";

?>
<form action="statistiche.php"> <input type="submit" value=TORNA> </form>
<div>


</form>


<script>
var chart = new CanvasJS.Chart("chartContainer1",
    {
        animationEnabled: true,
        title: {
            text: "PERSONE PER FASCE DI ETA'",
        },
        data: [
        {
            type: "pie",
            yValueFormatString: "##0.00\"%\"",
		    indexLabel: "{label} {y}",
            showInLegend: true,
            dataPoints: [
                { y: <?php echo (($minori20/$numero_persone)*100) ?>, legendText: "<?php echo "fino 20 anni: ".$minori20 ?>", label: "% numero dei minori di 20 anni" },
                { y: <?php echo (($persone20_40/$numero_persone)*100) ?>, legendText: "<?php echo "20 / 40 anni: ".$persone20_40 ?>", label: "% numero delle persone tra 20 e 40 anni" },
                { y: <?php echo (($persone_40_60/$numero_persone)*100) ?>, legendText: "<?php echo "40 / 60 anni: ".$persone_40_60 ?>", label: "% numero delle persone tra 40 e 60 anni" },
                { y: <?php echo (($maggiori60/$numero_persone)*100) ?>, legendText:"<?php echo "60 o più: ".$maggiori60 ?>", label: "% numero di persone sopra 60 anni" },
                { y: <?php echo (($sprovvisti/$numero_persone)*100) ?>, legendText:"<?php echo "senza età: ".$sprovvisti ?>", label: "% numero di persone senza età"}
            ]
        },
        ]
    });
chart.render();


</script>
