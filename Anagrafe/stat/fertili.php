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

//persone persone di sesso femminile
$query = "SELECT *  from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where  zone.NOME='$zona'  and persone.sesso='f'";
$result=$conn->query($query);
//echo  $query;

echo $conn->error.".";
if($result)
{
  $numero_persone_f=$result->num_rows;
 
}


//donne  in età fertile: si considera età fertile tra 13 anni e 40 anni: 
// 13 anni = 365 * 13 = 4745 giorni
// 40 anni = 365 * 40 = 14600 giorni
$query = "SELECT count(persone.id) from persone  
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where persone.sesso='f' and zone.NOME='$zona' and DATEDIFF('$oraoggi',data_nascita)>4745 and DATEDIFF('$oraoggi',data_nascita)<14600 
";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
 $row = $result->fetch_array();
 //echo " donne in eta fertile ";
 $etafertile= $row ["count(persone.id)"];
 $nonfertile=$numero_persone_f-$etafertile;
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

<div>
<div id="chartContainer1"   style="width: 70%;  height: 500px;  display: inline-block;"></div> 
<div>
<?php
echo "<form action='' method='GET' >";

echo "<select name='zona_richiesta'>";
echo "<option value='nord'>nord</option>";
echo "<option value='ovest'>ovest</option>";
echo "<option value='sud'>sud</option>";
echo "</select>";
echo "<input type='submit' name='invia'>";
echo "</form>";

?>

</div>
<div>
<?php
echo "</h2>";
echo "</br></br>Età media : ".(ceil($etamedia*10))/10;
echo "</h2>";
echo "</br>";
?>
<form action="statistiche.php"> <input type="submit" value=TORNA> </form>
<div>

</form>

<script>
var chart = new CanvasJS.Chart("chartContainer1",
    {
        animationEnabled: true,
        title: {
            text: "DONNE IN ETA' FERTILE (da 13 a 40 anni) nella zona <?php echo $zona ?>",
        },
        data: [
        {
            type: "pie",
            yValueFormatString: "##0.00\"%\"",
		    indexLabel: "{label} {y}",
            showInLegend: true,
            dataPoints: [
                
                { y:<?php echo (($etafertile/$numero_persone_f)*100) ?>, legendText: "<?php echo " fertili : ".$etafertile ?>", label: "% donne in età fertile" },
                { y:<?php echo (($nonfertile/$numero_persone_f)*100) ?>, legendText: "<?php echo "non fertili : ".$nonfertile ?>", label: "% donne non in età fertile" },
                
            ]
        },
        ]
    });
chart.render();


</script>
