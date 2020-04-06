<?php
/*
06/04/2020:Ferraiuolo: aggiunta immagine e id osm
Descrizione:mostra informazioni della casa in cui risiede la persona selezionata
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
?>
<html>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="mostra_casa_temp_css.css">
    <?php //stampaIntestazione(); ?>
    <body>

        <?php //stampaNavbar(); ?>
        <?php
        $util2 = $config_path .'/../db/db_conn.php';
        require_once $util2;
        ?>
        <?php stampaIntestazione(); ?>
        <?php stampaNavbar(); ?>
        <div id="myModal" class="modal">

            <!-- The Close Button -->
            <span class="close">&times;</span>

            <!-- Modal Content (The Image) -->
            <img class="modal-content" id="img01">


        </div>
        <?php
        $id_persona=$_POST["id_persona"];

        // selezione della casa in cui abita la persona passata in input
        $query1 =   "SELECT c.id id_casa,c.nome nome_casa, c.id_moranca id_moranca, m.nome nome_moranca, z.nome nome_zona,c.id_osm";
        $query1 .=   " FROM casa c ";
        $query1 .=  " INNER JOIN morance m  ON  c.id_moranca = m.id ";
        $query1 .=  " INNER JOIN pers_casa pc  ON c.id = pc.id_casa ";
        $query1 .=  " INNER JOIN persone p  ON  p.id = pc.id_pers ";
        $query1 .=  " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
        $query1 .=  " WHERE p.id = $id_persona ";

        //echo $query1;

        $result1 = $conn->query($query1);
        $row1 = $result1->fetch_array();
        $id_casa= $row1['id_casa'];

        $mystr = utf8_encode ($row1['nome_moranca']) ;

        echo "<br>ELENCO ABITANTI DELLA CASA CON ID $id_casa<br>";
        $immagine=glob('../case/immagini/'.$id_casa.'.*');//uso la funzione glob al posto di if_exist perchè permette di mettere * al posto dell'estensione.Se restituisce qualcosa ha trovato l'immagine.(il risultato è un array)
        if($immagine != null)
            echo "<div><img src='$immagine[0]' class='modal_image' style='display: block; margin-left:0px; margin-right: auto;width:100px;height:100px'></div> ";//$immagine è un array che conterrà una sola stringa (ad esempio: immagini/1.png) al posto numero 0


        echo "<br>NOME: '$row1[nome_casa]'<br><br>
        MORANCA: '$mystr'<br><br>
        ZONA: '$row1[nome_zona]'<br><br>";

        $osm_link = "https://www.openstreetmap.org/way/$row1[id_osm]";
        if ($row1['id_osm'] != null && $row1['id_osm'] != "0")
        { 
            echo "SU OPENSTREETMAP: $row1[id_osm]<a href=$osm_link target=new><i class='fa fa-map-marker'></i></a>"; 
        }
        else
        { 
            echo "SU OPENSTREETMAP: NON PRESENTE";
        }

        // elenco delle persone che abitano in quella casa
        $query = "SELECT c.id, c.nome,";
        $query .= " z.nome zona, c.id_moranca, m.nome nome_moranca, p.id id_pers, p.nominativo, ";
        $query .= " pc.cod_ruolo_pers_fam, rpf.descrizione desc_ruolo ";
        $query .= " FROM casa c INNER JOIN morance m  ON  c.id_moranca = m.id ";
        $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
        $query .= " INNER JOIN pers_casa pc  ON  pc.id_casa = c.id ";
        $query .= " INNER JOIN ruolo_pers_fam rpf   ON  pc.cod_ruolo_pers_fam = rpf.cod ";
        $query .= " INNER JOIN persone p  ON  pc.id_pers = p.id ";
        $query .= " AND c.id = $id_casa";

        $result = $conn->query($query);

        //echo $query;


        if ($result->num_rows !=0)
        {     
            echo "<table border>";
            echo "<tr>";
            echo "<th>id persona</th>";
            echo "<th>nominativo</th>";
            echo "<th>ruolo</th>";
            echo "</tr>";
            $cnt=0;
            while ($row = $result->fetch_array())
            {
                echo "<tr>";
                echo "<td>$row[id_pers]</th>";
                $mystr = utf8_encode ($row['nominativo']) ;
                echo "<td>$mystr</th>";
                echo "<td>$row[cod_ruolo_pers_fam]- $row[desc_ruolo]</th>";
                echo "</tr>";                    
            }
            echo "<br></table>";
        }
        else
            echo " Nessuna casa &egrave; presente nel database.";

        $result->free();
        $conn->close();	
        ?>
        <br>  

        <script>
            // Get the modal
            var modal = document.getElementById("myModal");

            // Prende l'immagine e le inserisce nel div modal 
            var img = document.getElementsByClassName('modal_image');
            for(var i=0; i<img.length; i++){
                var modalImg = document.getElementById("img01");
                var captionText = document.getElementById("caption");
                img[i].addEventListener('click',function(){
                    modal.style.display = "block";
                    modalImg.src = this.src;
                    captionText.innerHTML = this.alt;
                })
            }

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close")[0];

            // When the user clicks on <span> (x), close the modal
            span.onclick = function() { 
                modal.style.display = "none";
            }
        </script>
    </body>

</html>