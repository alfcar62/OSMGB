<?php
$config_path = __DIR__;
$util = $config_path . '/../util.php';
require $util;
setup();
?>

<html>
<?php stampaIntestazione(); ?>

<body>
    <?php stampaNavbar();
    ?>
    <?php
    $util = $config_path . '/../db/db_conn.php';
    require $util;
    ?>

    <?php
    $oraoggi = date("Y/m/d");
   
    //media età delle persone
    $query = "select avg(DATEDIFF('2020/2/29',data_nascita)) from persone";
    $result = $conn->query($query);
    //echo  $query;
    echo $conn->error;
    if ($result) {
        $row = $result->fetch_array();
        //echo " media eta delle persone: ";
        $etamedia = floor(($row["avg(DATEDIFF('2020/2/29',data_nascita))"] / 365));
    }



    //persone in totale nella zona
    $query = "SELECT zone.NOME as zona,count(persone.id) as numero  from persone 
inner join pers_casa on pers_casa.ID_PERS=persone.ID 
inner join casa on pers_casa.ID_casa=casa.ID
inner join morance on casa.ID_moranca=morance.ID
inner join zone on morance.cod_zona=zone.COD
GROUP BY zone.NOME ";
    $result = $conn->query($query);
    //echo  $query;

    echo $conn->error . ".";
    if ($result) {
        while ($row = $result->fetch_array()) {
            $elenco[$row["zona"]] = $row["numero"];
        }
        //  print_r($elenco);


    }


    $anno_corrente = date("yy");
    //echo $anno_corrente;


    ?>

    <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

    <div position="absolute" align="center">
        <div id="chartContainer1" style="width: 70%;  height: 500px;  display: inline-block;"></div>

    </div>
    <div style=' text-align: center;'>
        <?php
        echo "</h2>";
        echo "</br></br>Età media : " . (ceil($etamedia * 10)) / 10;
        echo "</h2>";

        ?>
        <form action="statistiche.php"> <input type="submit" value=TORNA> </form>
        <div>


            </form>


            <script>
                var chart = new CanvasJS.Chart("chartContainer1", {
                    animationEnabled: true,
                    title: {
                        text: "PERSONE PER ZONA",
                    },
                    data: [{
                        type: "pie",
                        showInLegend: true,
                        dataPoints: [{
                                y: <?php echo $elenco["SUD"] ?>,
                                legendText: "<?php echo "abitanti zona sud: " . $elenco["SUD"] ?>",
                                indexLabel: "numero di abitanti nella zona SUD"
                            },
                            {
                                y: <?php echo $elenco["NORD"] ?>,
                                legendText: "<?php echo "abitanti zona NORD: " . $elenco["NORD"] ?>",
                                indexLabel: "numero di abitanti nella zona NORD"
                            },
                            {
                                y: <?php echo $elenco["OVEST"] ?>,
                                legendText: "<?php echo "abitanti zona NORD: " . $elenco["OVEST"] ?>",
                                indexLabel: "numero di abitanti nella zona OVEST"
                            },
                        ]
                    }, ]
                });
                chart.render();
            </script>