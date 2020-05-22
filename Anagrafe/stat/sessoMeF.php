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


//persone in totale
$zona=$_GET['zona_richiesta'];
$query = "SELECT * from persone
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where zone.NOME='$zona' ";
$result=$conn->query($query);
//echo  $query;

echo $conn->error.".";
if($result)
{
  $numero_persone=$result->num_rows;
}





//persone di sesso femminile
$zona =$_GET['zona_richiesta'];
$query = "SELECT * from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where persone.sesso='f' and zone.NOME='$zona' 
";



$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
  $numero_persone_m=$result->num_rows;
}




//persone di sesso maschile
$query = "SELECT * from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
where persone.sesso='m' and zone.NOME='$zona' 
";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
  $numero_persone_f=$result->num_rows;
}

//$numero_nc=($numero_persone-($numero_persone_m+$numero_persone_f)); //persone strane


//media età delle persone 
$query = " select avg(DATEDIFF(CURDATE(),data_nascita)) as etamedia from persone";
$query .= " inner join pers_casa on pers_casa.ID_PERS=persone.ID ";
$query .= " inner join casa on pers_casa.ID_casa=casa.ID";
$query .= " inner join morance on casa.ID_moranca=morance.ID";
$query .= " inner join zone on morance.cod_zona=zone.COD";
$query .= " where  zone.NOME='$zona' ";
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

<div position="absolute"  align="center">
<div id="chartContainer1"   style="width: 70%;  height: 500px;  display: inline-block;"></div> 

</div>
<div>
<?php
echo "</h2>";
echo "</br></br>Età media : ".(ceil($etamedia*10))/10;
echo "</h2>";
echo "<form action='' method='GET' >";
echo "<select name='zona_richiesta'>";
echo "<option value='nord'>nord</option>";
echo "<option value='ovest'>ovest</option>";
echo "<option value='sud'>sud</option>
</select>
<input type='submit' name='invia'>
</form>";
echo "</br>";
//echo "selezionato ".$_POST["zona_richiesta"];


?>
<form action="statistiche.php"> <input type="submit" value=TORNA> </form>
<div>

</form>

<script>
var chart = new CanvasJS.Chart("chartContainer1",
    {
        animationEnabled: true,
        title: {
            text: "PERCENTUALE DI DONNE E MASCHI NELLA ZONA <?php echo $zona ?>",
        },
        data: [
        {
            type: "pie",
            showInLegend: false,
            dataPoints: [
                { y: 0, legendText: "prova",  },
                { y:0, legendText: "prova", },
                { y: <?php echo (ceil(($numero_persone_f/$numero_persone)*100)) ?>, legendText:" <?php echo "femmine ".$numero_persone_f ?>", indexLabel:" <?php echo "% numero femmine" ?>" }, 
                { y: <?php echo(floor(($numero_persone_m/$numero_persone)*100)) ?>, legendText: "<?php echo "maschi ".$numero_persone_m ?>", indexLabel: "% numero maschi" },

            ]
        },
        ]
    });
chart.render();


</script>
