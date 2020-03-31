<?php
/* Autore:Ferraiuolo
*** Descrizione:Gestione delle case
*** 13/03/2020  Carlone: modificata la query (per visualizzare anche se non c'è il capo famiglia)
*** 11/03/2020 Ferraiuolo  Modifica:aggiunta visualizzazione della casa con relativo zoom in caso si passi 
*** con il cursore sopra
***29/03/2020: Ferraiuolo: aggiunta del div modal,script js per creare lo zoom quando si clicca sulla foto della casa
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
unsetPag(basename(__FILE__)); 

?>
<html>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="gest_case_temp_css.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <!--<script src="gest_case_js.js"></script>-->
    <script type="text/javascript">
        $(document).ready(function(){
            $('.search-box input[type="text"]').on("keyup input", function(){
                /* Get input value on change */
                var inputVal = $(this).val();
                var resultDropdown = $(this).siblings(".result");
                if(inputVal.length){
                    $.get("cerca_casa.php", {term: inputVal}).done(function(data){
                        // Display the returned data in browser
                        resultDropdown.html(data);
                    });
                } else{
                    resultDropdown.empty();
                }
            });

            // Set search input value on click of result item
            $(document).on("click", ".result p", function(){
                $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
                $(this).parent(".result").empty();
            });
        });
    </script>

    <?php
    $util2 = $config_path .'/../db/db_conn.php';
    require_once $util2;
    ?>
    <?php stampaIntestazione(); ?>
    <body>
        <?php stampaNavbar(); ?>
        <div class="search-box">
            <input type="text" autocomplete="off" placeholder="Ricerca casa..." />
            <div class="result"></div>
        </div>
        <div id="lb-back">
            <div id="lb-img"></div>
        </div>
        <!-- Modal:div che compare quando si clicca sull'immagine -->
        <div id="myModal" class="modal">

            <!-- The Close Button -->
            <span class="close">&times;</span>

            <!-- Modal Content (The Image) -->
            <img class="modal-content" id="img01">


        </div>
        <?php 

        // modificato per la gestione corretta della paginazione (A.C. 10/3/2020)
        // se $_POST['cod_zona'] valorizzato --> arriva  dall'action form
        // se $_SESSION  valorizzato --> arriva  dal $SERVER[PHP_SELF]
        if (isset($_POST['cod_zona']))
        {
            $cod_zona = $_POST['cod_zona']; 
            $_SESSION['cod_zona'] = $cod_zona;
        }  
        else 
        {
            $cod_zona = "tutte";
        } 

        //echo " cod_zona = ". $cod_zona;
        //echo " SESSION(cod_zona) = ".$_SESSION['cod_zona'];

        // Creo una variabile dove imposto il numero di record 
        // da mostrare in ogni pagina
        $x_pag = 10;

        // Recupero il numero di pagina corrente.
        // Generalmente si utilizza una querystring

      
        
        $pag=Paginazione("pag_c");

        // Controllo se $pag è valorizzato e se è numerico
        // ...in caso contrario gli assegno valore 1
        if (!$pag || !is_numeric($pag)) $pag = 1; 

        // Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella agenda


        /*$query = "SELECT count(c.id) as cont FROM casa c";
        if (isset($cod_zona) && ($cod_zona != 'tutte'))
        {  
            $query .= " inner join morance m on m.id = c.id_moranca ";
            $query .= " inner join zone z on m.cod_zona = z.cod";
            $query .= " AND z.cod = '$cod_zona'"; 
        }
        //echo $query;
        */
        $query = "SELECT count(c.id) as cont";
        $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
        $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
        $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
        $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
        $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
        $query .= " WHERE c.DATA_FINE_VAL is null";
        if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '{$cod_zona}'";
        
        $result = $conn->query($query);
        $row = $result->fetch_array();
        $all_rows= $row['cont'];

        //  definisco il numero totale di pagine
        $all_pages = ceil($all_rows / $x_pag);


        // Calcolo da quale record iniziare
        $first = ($pag - 1) * $x_pag;

        echo "<h2> Villaggio di NTchangue</h2>";
        echo "<br> ELENCO CASE <br>";
        echo "<a href='ins_casa.php'><br>";
        echo "Aggiungi nuova casa </a><br><br>";


        //Select option per la scelta della zona
        echo "<form action='gest_case.php' method='POST'><br>";
        echo   "Selezione Zona : <select name='cod_zona'>";
        $result = $conn->query("SELECT * FROM zone");
        $nz=$result->num_rows;
        echo "<option value='tutte'>  tutte </option>";
        for($i=0;$i<$nz;$i++)
        {
            $row = $result->fetch_array();

            if(isset($cod_zona) && $cod_zona == $row["COD"])
                echo "<option value='".$row["COD"]."' selected>". $row["NOME"]." </option>";
            else
                echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
        }
        echo "</select>";
        echo " <input type='submit' value='Conferma'>";
        echo " </form>";

        /*
*** 13/3/2020: A. Carlone. Modificata la query, per visualizzare anche case senza capo famiglia
*/
        $query = "SELECT c.id, c.nome,";
        $query .= " z.nome zona, c.id_moranca, m.nome nome_moranca,";
        $query .= " c.nome, p.id id_pers, p.nominativo, c.id_osm as id_osm, ";
        $query .= " c.data_inizio_val data_val, c.data_fine_val";
        $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
        $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
        $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
        $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
        $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
        $query .= " WHERE c.DATA_FINE_VAL is null";
        if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '{$cod_zona}'";
        $query .= " ORDER BY c.id ASC";
        $query .= " LIMIT $first, $x_pag";
        $result = $conn->query($query);  
        //echo $query;

        if ($result->num_rows !=0)
        {
            echo "<table border>";
            echo "<tr>";
            echo "<th>Foto</th>";
            echo "<th>id</th>";
            echo "<th>nome</th>";
            echo "<th>zona</th>";
            echo "<th>id moranca</th>";
            echo "<th>moran&ccedil;a</th>";
            echo "<th>id capo famiglia</th>";
            echo "<th>capo famiglia</th>";
            echo "<th>Abitanti</th>";
            echo "<th>su OpenStreetMap</th>";
            echo "<th>data val</th>";
            echo "<th>Modifica</th>";
            echo "<th>Elimina</th>";
            echo "<th>Persone </th>";
            echo "<th>Storico </th>";
            echo "</tr>";

            while ($row = $result->fetch_array())
            {
                echo "<tr>";
                $immagine=glob('immagini/'.$row['id'].'.*');//uso la funzione glob al posto di if_exist perchè permette di mettere * al posto dell'estensione.Se restituisce qualcosa ha trovato l'immagine.(il risultato è un array)
                if($immagine != null)
                    echo "<td><div ><img src='$immagine[0]' class='modal_image' style='display: block; margin-left: auto; margin-right: auto;width:35px;height:30px'  ></div></td> ";//$immagine è un array che conterrà una sola stringa (ad esempio: immagini/1.png) al posto numero 0

                else{
                    echo '<td><i class="fa fa-image"></i></td>';
                }
                echo "<td>$row[id]</td>";
                echo "<td>$row[nome]</td>";
                echo "<td>$row[zona]</td>";
                echo "<td>$row[id_moranca]</td>";
                $mystr = utf8_encode ($row['nominativo']) ;

                echo "<td>$mystr</td>";
                echo "<td>$row[id_pers]</td>";

                $mystr = utf8_encode ($row['nome_moranca']) ;
                echo "<td>$mystr</th>";


                $query2="SELECT COUNT(pers_casa.ID_PERS) as persone from pers_casa WHERE ID_CASA='$row[id]'";
                $result2 = $conn->query($query2);
                $row2 = $result2->fetch_array();
                echo "<td>$row2[persone]</th>";


                $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
                if ($row['id_osm'] != null && $row['id_osm'] != "0")
                { 
                    echo "<td>$row[id_osm]<a href=$osm_link target=new><i class='fa fa-map-marker'></i></a></td>"; 
                }
                else
                { 
                    echo "<td>&nbsp;</td>";
                }
                echo "<td>$row[data_val]</td>";

                echo " <form method='post' action='mod_casa.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";

                echo " <form method='post' action='del_casa.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";

                echo " <form method='post' action='mostra_persone.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i></button> ". "</th></form>";

                echo " <form method='post' action='vis_casa_sto.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i></button> ". "</th></form>";
                echo "</tr></form>";
            }
            echo "</table>";
        }
        else{
            if(isset($cod_zona)){
                echo " Nessuna casa &egrave; presente nel database nella zona selezionata ";
            }
            else{
                echo " Nessuna casa &egrave; presente nel database";
            }
        }

        echo "<br> Numero case: $all_rows<br>";


        // Se le pagine totali sono più di 1...
        // stampo i link per andare avanti e indietro tra le diverse pagine!
        if ($all_pages > 1){
            if ($pag > 1){
                echo "<br><a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag - 1) . "\">";
                echo "Pagina Indietro</a>&nbsp;<br>";
            }
            // faccio un ciclo di tutte le pagine
            $cont=0;
            for ($p=1; $p<=$all_pages; $p++) 
            {
                if ($cont>=50)
                {
                    echo "<br>";
                    $cont=0;
                }
                $cont++;
                // per la pagina corrente non mostro nessun link ma la evidenzio in bold
                // all'interno della sequenza delle pagine
                if ($p == $pag) echo "<b>" . $p . "</b>&nbsp;";
                // per tutte le altre pagine stampo il link
                else
                { 
                    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $p . "\">";
                    echo $p . "</a>&nbsp;";
                } 
            }
            if ($all_pages > $pag)
            {
                echo "<br><br><a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag + 1) . "\">";
                echo "Pagina Avanti<br></a>";
            } 
        }

        $result->free();
        $conn->close();	
        ?>  

    </body>
    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Prende l'immagine e le inserisce nel div modal (codice di W3Schools modificato con l'aggiunta delle classi)
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

</html>