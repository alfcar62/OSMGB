<?php
//aggiunta la paginazione della tabella delle morance

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
<a href='statistiche_zona.php'>Statistiche per zona <i class="fa fa-pie-chart" aria-hidden="true"></i></a>
<a href='morti.php'>Statistiche decessi <i class="fa fa-pie-chart" aria-hidden="true"></i></a>
<br>
<?php
$util = $config_path .'/../db/db_conn.php';
require $util;
?>

<?php
//persone in totale
$query = "SELECT * from persone";
$result=$conn->query($query);
//echo  $query;

//echo $conn->error.".";
if($result)
{
  $numero_persone=$result->num_rows;
 // echo $numero_persone;
}

//persone minorenni sul totale
$oraoggi=date("Y/m/d");
$query = "SELECT count(id) from persone where DATEDIFF('$oraoggi',data_nascita)<6570 ";   //6570 è il numero di giorni che una persona vive fino a 18 anni
$result=$conn->query($query);
//echo  $query;
echo $conn->error;

if($result)
{
$row = $result->fetch_array();
//echo " persone minori di 18 anni ";
$minorenni=$row ["count(id)"];
$maggiorenni=$numero_persone-$minorenni;
//echo $row ["count(id)"];
}
//persone per moranca

//abitanti in ogni casa
$query = "SELECT count(id) from  casa";   //conta il numero di case
$result=$conn->query($query);
//echo  $query;
echo $conn->error;

if($result)
{
$row = $result->fetch_array();
$persone_casa=$numero_persone/$row ["count(id)"];

}

//persone con età maggiore di 40 anni
$query = "SELECT count(id) from persone where DATEDIFF('$oraoggi',data_nascita)>14600";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone superiori a 40 anni ";
$maggiori40=$row ["count(id)"];
//echo $row ["count(id)"];
}

//persone di sesso maschile
$query = "SELECT * from persone where sesso='m' ";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
  $numero_persone_m=$result->num_rows;
}

//persone di sesso femminile
$query = "SELECT * from persone where sesso='f' ";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
  $numero_persone_f=$result->num_rows;
}

//$numero_nc=($numero_persone-($numero_persone_m+$numero_persone_f)); //persone strane

//media età delle persone 
$query = "select avg(DATEDIFF('2020/2/29',data_nascita)) from persone";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " media eta delle persone: ";
$etamedia=floor(($row ["avg(DATEDIFF('2020/2/29',data_nascita))"]/365));
}


//donne  in età fertile 
$query = "SELECT count(id) from persone where DATEDIFF('$oraoggi',data_nascita)>5475 and DATEDIFF('$oraoggi',data_nascita)<16425 and sesso='f' ";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " donne in eta fertile ";
$etafertile= $row ["count(id)"];
$nonfertile=$numero_persone_f-$etafertile;
}

//persone con età minore  di 20 anni
$query = "SELECT count(id) from persone where DATEDIFF('$oraoggi',data_nascita)>=0 and DATEDIFF('$oraoggi',data_nascita)<= 7300";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone superiori a 20 anni ";
$minori20=$row ["count(id)"];
//echo $row ["count(id)"];
}

//persone con età tra i 20 e i 40
$query = "SELECT count(id) from persone where  DATEDIFF('$oraoggi',data_nascita)>7300 and DATEDIFF('$oraoggi',data_nascita)<=14600";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone con età tra 20 e 40 anni ";
$persone20_40=$row ["count(id)"];
//echo $row ["count(id)"];
}
                     

//persone tra 40 e 60
$query = "SELECT count(id) from persone where  DATEDIFF('$oraoggi',data_nascita)>14600 and DATEDIFF('$oraoggi',data_nascita)<=21900";
$result=$conn->query($query);
//echo  $query;
echo $conn->error;
if($result)
{
$row = $result->fetch_array();
$persone_40_60=$row ["count(id)"];

}

//persone morte 
$query="SELECT count(Tab1.Indice) as MORTI 
from (( 
select persone.ID as Indice,persone.NOMINATIVO as NOMI from persone where persone.DATA_MORTE is not null 
UNION select persone_sto.ID as ID,persone_sto.NOMINATIVO as NOMI from persone_sto where persone_sto.DATA_MORTE is not null 
) as Tab1);";   
$result=$conn->query($query);
//echo  $query;
//echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " ";
$morti=$row ["MORTI"];
}


//persone con età maggiore di 60 anni
$query = "SELECT count(id) from persone where DATEDIFF('$oraoggi',data_nascita)>21900";
$result=$conn->query($query);
//echo  $query;
//echo $conn->error;
if($result)
{
$row = $result->fetch_array();
//echo " persone superiori a 60 anni ";
$maggiori60=$row ["count(id)"];

}
$sprovvisti=$numero_persone-($minori20+$persone20_40+$persone_40_60+$maggiori60);

$anno_corrente=date("yy");
//echo $anno_corrente;
?>
<h2><center> Dettaglio Statistiche<IMG SRC="../img/inserisci2.png"> </center></h2>
<h2>
Statistiche per anno <IMG SRC="../img/inserisci2.png">
</h2>
<div style="float:left; display:block; width:1200px;">
<form  action="" method="post" >
Anno: 
<?php 

if(isset($_POST['anno_persone'])){ 
    $annata=$_POST['anno_persone'];
    $query = "SELECT * FROM `persone` WHERE year(DATA_NASCITA) = '$annata'";
    $result=$conn->query($query);
    //echo  $query;
    if($result)
    {
      $numero_persone_annata=$result->num_rows;      
    }  
    }
?>

<select name="anno_persone">

<?php 
//persone nato per anno
$anno=1940;
if(isset($_POST['anno_persone'])){ 
   $attuale =$_POST['anno_persone'];
    echo "<option value='$attuale'>$attuale</option>"; 
} 
while($anno<=$anno_corrente)
{
   echo "<option value='$anno'>$anno</option>"; 
   $anno++;
}

echo"</select>";
echo " sono nate:";
echo"<input type='text' readonly value='";
if(isset($numero_persone_annata))
{echo $numero_persone_annata;}
echo "'>  persone";
echo"<input type='submit' class='button' name='invio' value='mostra'>";
echo "</form>";
echo "</div>";

echo "<div style='float:left; display:block; width:900px;'>";
echo "<h2>Statistiche decessi<IMG SRC='../img/inserisci2.png'></h2>";
?>
<div style="float:left; display:block; width:900px; ">
<form name='form' id='form' action="#indice1" method="post" >
<section id="#indice1"></section>
<label for='anno'>Nell'anno</label>  
<?php 
//persone morte
if(isset($_POST['anno_persone2'])){ 
    $annata2=$_POST['anno_persone2'];
    $query = "SELECT * FROM `persone` WHERE year(DATA_MORTE) = '$annata2'";
    $result=$conn->query($query);
    //echo  $query;
    echo $conn->error;
    if($result)
    {
      $numero_persone_annata2=$result->num_rows;    
    }   
   } 
?>

<select name="anno_persone2">

<?php 
$anno2=1940;
if(isset($_POST['anno_persone2'])){ 
   $attuale =$_POST['anno_persone2'];
    echo "<option value='$attuale2'>$attuale2</option>"; 
} 
while($anno2<=$anno_corrente)
{  
   echo "<option value='$anno2'>$anno2</option>"; 
   $anno2++;
}

echo"</select>";
echo " sono decedute:";
echo"<input type='text' readonly value='";
if(isset($numero_persone_annata2))
{echo $numero_persone_annata2;}
echo "'>  persone";
echo "<input type='submit' class='button' value='mostra'><br>";
echo "</form>";
echo "</div>";
echo "<br>";

echo "<h2>Statistiche complessive<IMG SRC='../img/inserisci2.png'></h2>";
echo "</br>numero abitanti per casa : ".(ceil($persone_casa*10))/10;
echo "</br>Età media della popolazione : ".(ceil($etamedia*10))/10;
echo "</br>Persone decedute dall'inizio : ".$morti;

echo "</body>";
echo "</html>";
?>
<!--
script dei grafici con integrazione in php dei dati necessari
-->
<script>
var chart = new CanvasJS.Chart("chartContainer1",
    {
        animationEnabled: true,
        title: {
            text: "PERCENTUALI  MASCHILE  E FEMMINILE",
        },
        data: [
        {
            type: "pie",
            yValueFormatString: "##0.00\"%\"",
		    indexLabel: "{label} {y}",
            showInLegend: true,
            dataPoints: [
                { y: <?php echo (ceil(($numero_persone_f/$numero_persone)*100)) ?>, legendText:" <?php echo "femmine ".$numero_persone_f ?>", label:" <?php echo "% numero femmine" ?>" }, 
                { y: <?php echo(floor(($numero_persone_m/$numero_persone)*100)) ?>, legendText: "<?php echo "maschi ".$numero_persone_m ?>", label: "% numero maschi" },
            ]
        },
        ]
    });
chart.render();
var chart = new CanvasJS.Chart("chartContainer2",
    {
        animationEnabled: true,
        title: {
            text: "PERCENTUALE MINORENNI E MAGGIORENNI",
        },
        data: [
        {
            type: "pie",
            showInLegend: true,
            dataPoints: [
                { y: <?php echo (($minorenni/$numero_persone)*100) ?>, legendText:" <?php echo "minorenni: ".$minorenni ?>", indexLabel:" <?php echo "% numero minorenni" ?>"}, 
                { y: <?php echo(($maggiorenni/$numero_persone)*100) ?>, legendText: "<?php echo "maggiorenni: ". $maggiorenni ?>", indexLabel: "% numero maggiorenni" },
            ]
        },
        ]
    });
chart.render();
var chart = new CanvasJS.Chart("chartContainer3",
    {
        animationEnabled: true,
        title: {
            text: "PERSONE PER FASCE DI ETA'",
        },
        data: [
        {
            type: "pie",
            showInLegend: true,
            dataPoints: [
                { y: <?php echo (($minori20/$numero_persone)*100) ?>, legendText: "<?php echo "fino 20 anni: ".$minori20 ?>", indexLabel: "% numero dei minori di 20 anni" },
                { y: <?php echo (($persone20_40/$numero_persone)*100) ?>, legendText: "<?php echo "20 / 40 anni: ".$persone20_40 ?>", indexLabel: "% numero delle persone tra 20 e 40 anni" },
                { y: <?php echo (($persone_40_60/$numero_persone)*100) ?>, legendText: "<?php echo "40 / 60 anni: ".$persone_40_60 ?>", indexLabel: "% numero delle persone tra 40 e 60 anni" },
                { y: <?php echo (($maggiori60/$numero_persone)*100) ?>, legendText:"<?php echo "60 o più: ".$maggiori60 ?>", indexLabel: "% numero di persone sopra 60 anni" },
                { y: <?php echo (($sprovvisti/$numero_persone)*100) ?>, legendText:"<?php echo "senza età: ".$sprovvisti ?>", indexLabel: "% numero di persone senza età"}
            ]
        },
        ]
    });
chart.render();
var chart = new CanvasJS.Chart("chartContainer4",
    {
        animationEnabled: true,
        title: {
            text: "DONNE FERTILI ",
        },
        data: [
        {
            type: "pie",
            showInLegend: true,
            dataPoints: [
                
                { y:<?php echo (($etafertile/$numero_persone_f)*100) ?>, legendText: "<?php echo " fertili : ".$etafertile ?>", indexLabel: "% donne in età fertile" },
                { y:<?php echo (($nonfertile/$numero_persone_f)*100) ?>, legendText: "<?php echo "non fertili : ".$nonfertile ?>", indexLabel: "% donne non in età fertile" },
                
            ]
        },
        ]
    });
chart.render();

</script>

