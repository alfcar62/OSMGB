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
//persone morte in totale
$query = "SELECT count(Tab1.NOMI) as MORTI 
from (
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null 
) as Tab1;";  
$result=$conn->query($query);
//echo  $query;

echo $conn->error.".";
if($result)
{
$row = $result->fetch_array();
$morti_totali=$row["MORTI"];
//echo $morti_totali;
}




//numero morti tra 0 e 1 anno
//il datediff esprime la differenza tra due date.In questo caso in giorni
$query = "SELECT count(Tab1.NOMI) as MORTI 
from (
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>0 and  DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)<= 365
) as Tab1;";  


$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
$morti_0_1=$row["MORTI"];
//echo $morti_0_1;
}





//morti tra 1 e 5 anni
//il datediff esprime la differenza tra due date.In questo caso in giorni
$query = "SELECT count(Tab1.NOMI) as MORTI 
from (
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>365  and  DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)<= 1825
) as Tab1;"; 
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
echo " morti da 1 a 5 anni";
$morti_1_5=$row ["MORTI"];
//echo $morti_1_5;


}



//morti tra 5 e 10 anni
//il datediff esprime la differenza tra due date.In questo caso in giorni
$query = "SELECT count(Tab1.NOMI) as MORTI 
from (
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>1825  and  DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)<= 3650
) as Tab1;"; 
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " ";
$morti_5_10=$row ["MORTI"];
//echo $morti_5_10;


}
 


//morti tra 10 e 20 anni
//il datediff esprime la differenza tra due date.In questo caso in giorni
$query = "SELECT count(NOMI) as MORTI 
from (
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>3650  and  DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)<= 7300
) as Tab1;"; 
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " ";
$morti_10_20=$row ["MORTI"];
//echo $morti_10_20;


}

//morti tra 20 e 40 anni
//il datediff esprime la differenza tra due date.In questo caso espressa in giorni
$query = "SELECT count(tab1.NOMI) as MORTI
from
(select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>7300  and  DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)<= 14600) as tab1
;"; 
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " ";
$morti_20_40=$row ["MORTI"];
//echo $morti_20_40;
}


//morti tra 40 e 60 anni
//il datediff esprime la differenza tra due date.In questo caso espressa in giorni
$query = "SELECT count(Tab1.NOMI) as MORTI 
from (
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>14600  and  DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)<= 21900
) as Tab1;"; 
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " ";
$morti_40_60=$row ["MORTI"];
//echo $morti_40_60;

}


//morti  60 anni
//il datediff esprime la differenza tra due date.In questo caso espressa in giorni
$query = "SELECT count(Tab1.NOMI) as MORTI 
from ( 
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone 
where persone.DATA_MORTE is not null and DATEDIFF(persone.DATA_MORTE,persone.DATA_NASCITA)>21900  
) as Tab1;"; 
$result=$conn->query($query);

echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " ";
$morti_60=$row ["MORTI"];
//echo $morti_60;


}


$anno_corrente=date("yy");










?>

<script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

<div position="absolute"  align="center">
<div id="chartContainer1"   style="width: 70%;  height: 500px;  display: inline-block;"></div> 

</div>
<div style=' text-align: center;'>

<?php
//media età delle persone 
$query = "select avg(DATEDIFF('$oraoggi',data_nascita)) from persone";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " media eta delle persone: ";
$etamedia=floor(($row ["avg(DATEDIFF('$oraoggi',data_nascita))"]/365));
}
echo "</h2>";
echo "</br></br>Età media : ".(ceil($etamedia*10))/10;
echo "</h2>";

echo "</br>";/* 
echo "<form action='' method='GET' >";

echo "<select name='zona_richiesta'>";
echo "<option value='$zona'>$zona</option>";
echo "<option value='nord'>nord</option>";
echo "<option value='ovest'>ovest</option>";
echo "<option value='sud'>sud</option>
</select>
<input type='submit' name='invia'>
</form>";*/

?>
<form action="statistiche.php"> <input type="submit" value=TORNA> </form>
<div>


</form>


<script>
var chart = new CanvasJS.Chart("chartContainer1",
    {
        animationEnabled: true,
        title: {
            text: "MORTALITA' PER FASCE DI ETA'",
        },
        data: [
        {
            type: "pie",
            showInLegend: true,
            dataPoints: [
                { y: <?php echo (($morti_0_1/$morti_totali)*100) ?>, legendText: "<?php echo "deceduti neonatale: ".$morti_0_1 ?>", indexLabel: "% deceduti neonatale" },
                { y: <?php echo (($morti_1_5/$morti_totali)*100) ?>, legendText: "<?php echo "deceduti 1 5 anni: ".$morti_1_5 ?>", indexLabel: "% deceduti da 1 a 5 anni" },
                { y: <?php echo (($morti_5_10/$morti_totali)*100) ?>, legendText: "<?php echo "deceduti 5 10 anni: ".$morti_5_10 ?>", indexLabel: "% deceduti da 5 a 10 anni" },
                { y: <?php echo (($morti_10_20/$morti_totali)*100) ?>, legendText:"<?php echo "deceduti 10 20 anni: ".$morti_10_20 ?>", indexLabel: "% deceduti da 10 a 20 anni" },
                { y: <?php echo (($morti_20_40/$morti_totali)*100) ?>, legendText:"<?php echo "deceduti 20 40 anni: ".$morti_20_40 ?>", indexLabel: "% deceduti da 20 a 40 anni"},
                { y: <?php echo (($morti_40_60/$morti_totali)*100) ?>, legendText:"<?php echo "deceduti 40 60 anni: ".$morti_40_60 ?>", indexLabel: "% deceduti da 40 a 60 anni"},
                { y: <?php echo (($morti_60/$morti_totali)*100) ?>, legendText:"<?php echo "deceduti sopra 60 anni: ".$morti_60 ?>", indexLabel: "% deceduti da 60 anni in su"}
            ]
        },
        ]
    });
chart.render();


</script>
