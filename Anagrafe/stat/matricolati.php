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
<?php
$util = $config_path .'/../db/db_conn.php';
require $util;
?>

<?php

$oraoggi=date("Y/m/d");
$zona=strtoupper($_GET["zona_richiesta"]);

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
//persone con matricola per zona
$query = "SELECT count(persone.ID) from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona' and persone.matricola_stud is not null ";
//echo $query;    
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();

$matricolati= $row ["count(persone.ID)"];
$no_matricola=$numero_persone-$matricolati;


}


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

?>

<script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<br>
<a href='statistiche_det.php'> Dettaglio Statistiche <IMG SRC="../img/inserisci2.png"></a>
&nbsp;&nbsp;
<a href='statistiche_zona.php'>Statistiche per zona <i class="fa fa-pie-chart" aria-hidden="true"></i></a>
<br>

<div position="absolute"  align="center">
<div id="chartContainer1"   style="width: 70%;  height: 500px;  display: inline-block;"></div> 

</div>
<div style=' text-align: center;'>
<?php
echo "</h2>";
echo "</br></br>Età media : ".(ceil($etamedia*10))/10;
echo "</h2>";

echo "</br>";
echo "<form action='' method='get' >";

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
            text: "STUDENTI IMMATRICOLATI NELLA ZONA <?php echo $zona ?>",
        },
        data: [
        {
            type: "pie",
            showInLegend: true,
            dataPoints: [
                
                { y:<?php echo (($matricolati/$numero_persone)*100) ?>, legendText: "<?php echo " immatricolati : ".$matricolati ?>", indexLabel: "% immatricolati" },
                { y:<?php echo (($no_matricola/$numero_persone)*100) ?>, legendText: "<?php echo "non immatricolati : ".$no_matricola ?>", indexLabel: "% non immatricolati" },       
            ]
        },
        ]
    });
chart.render();


</script>
