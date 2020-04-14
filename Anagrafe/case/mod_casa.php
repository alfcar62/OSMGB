<?php
/*
*** mod_casa.php
*** caso d'uso: modifica casa
***
*** Viene richiamato da gest_case.php  (input = POST(id_casa))
*** Chiede in un form i dati da  modificare sulla tabella casa 
*** ed attiva mod_casa.php
*** 14/3/2020: A.Carlone: modifiche varie
*** 11/3/2020: Ferraiuolo, Arneodo - aggiunta possibilità di caricare una foto della casa
*** 22/3/2020: Ferraiuolo:aggiunta possibilità di elimnare la foto
*/
$config_path = __DIR__;
//$util1 = "E:/xampp/htdocs/OSM/Anagrafe/util.php";
$util1="../util.php";
//$util2 = "E:/xampp/htdocs/OSM/Anagrafe/db/db_conn.php";
$util2="../db/db_conn.php";
require_once $util2;
require_once $util1;
setup();
$pag=$_SESSION['pag_c']['pag_c'];
?>
<?php stampaIntestazione(); ?>
<body>
    <?php stampaNavbar(); ?>

    <?php
    $id_casa=$_POST["id_casa"];

/*
***  seleziona i dati della casa da visualizzare  
*** 13/3/2020: A. Carlone. Modificata la query, per visualizzare anche case senza capo famiglia
*/
    $query = "SELECT c.id, c.nome as nome_casa,";
    $query .= " z.nome zona, c.id_moranca as id_moranca,";
    $query .= " m.nome nome_moranca, ";
    $query .= " p.id  as id_capo_famiglia, p.nominativo as capo_famiglia, c.id_osm, ";
    $query .= " c.data_inizio_val data_inizio, c.data_fine_val as data_fine";
    $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
    $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
    $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
    $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
    $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
    $query .= " WHERE c.id=$id_casa";
    $query .= " AND c.data_fine_val is null";
    $result = $conn->query($query);  
    //echo $query;

    $result = $conn->query($query);

    $row=$result->fetch_array();
    $nome_casa=$row["nome_casa"];
    $data_inizio=$row["data_inizio"];
    $data_fine=$row["data_fine"];
    $id_moranca=$row["id_moranca"];          
    $nome_moranca=$row["nome_moranca"];
    $id_osm=$row["id_osm"];
    if ($id_osm == '')
        $id_osm = 0;
    $capo_famiglia=$row["capo_famiglia"];
    $id_capo_famiglia=$row["id_capo_famiglia"];

    echo "<h3>Modifica casa: $nome_casa  (id= $id_casa)</h3>";
    echo "<form action='modifica_casa.php' method='POST'>";
    echo   " <input type='hidden' name='id_casa' value='$id_casa' >";
    echo   " <input type='hidden' name='data_inizio' value='$data_inizio' >";
    echo   " <input type='hidden' name='data_fine' value='$data_fine' >";
    echo "nome casa:&nbsp;&nbsp;<input type='text' name='nome_casa' value='$nome_casa' required><br>";         

/*
*** selezione moranca
*/
    echo 'moranca:&nbsp;';

    $query  = "SELECT id, nome FROM morance ";
    $query .= "WHERE nome != '$nome_moranca'";
    $query .= " ORDER BY nome ASC";
    //echo $query;
    $result = $conn->query($query);

    $nr=$result->num_rows;
    echo '<select name="moranca" required >';
    echo "<option value='$id_moranca'>$nome_moranca</option><br>";
    for($i=0;$i<$nr;$i++)
    {
        $row=$result->fetch_array();
        if($row['nome']!=null || $row['nome']!="")
        {
            $nome = utf8_encode ($row['nome']) ;
            echo "<option value="."'".$row["id"]."'".">".$nome."</option>";
        } 
    }
    echo "</select><br>";
 ?>

 id OSM: <input type='text' name='id_osm'><span id="info"><img onmouseover="tooltip(event)" onmouseout="tooltip(event)" src="../img/infoIcon.png" style="height:25px;width:50px;"></span>
 <span id="error" style="visibility:hidden">Identificativo della casa sulla mappa OpenStreetMap:<br> 1. vai sulla mappa OSM,<br> 2. cerca la casa,<br> 3. clicca con il pulsante destro del mouse, scegli 'ricerca elementi' <br>4.  copia qui il numero dell'oggetto relativo</span><br>

<?php
    echo "<input type='submit' class = 'button' value='Modifica'><br>";
    echo "</form>";


    //----------------------------UPLOAD DELLA FOTO-------------------------
    echo "<h2>MODIFICA LA FOTO DELLA CASA :</h2>";

    if(isset($_POST["caricaFoto"])) {
        $target_dir = "immagini/";
        $target_file = $target_dir .$id_casa.'.'.pathinfo($_FILES["fileToUpload"]["name"] ,PATHINFO_EXTENSION);
        $flagUpload = true; //flag che mi servirà alla fine per capire se è possibile caricare l'immagine
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        // Controllo se il file caricato è un immagine

        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);//se resituisce true è un immagine
        if($check == true) {
            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 500000) {
                echo "Errore: l'immagine è troppo grande";
                $flagUpload = false;
            }
            // Consento soltanto alcuni formati
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                echo "Errore: è consentito caricare soltanto JPG,PNG o JPEG";
                $flagUpload = false;
            }
            //Controllo il flag
            if ($flagUpload == true) {
                // Elimino eventuali file presenti con lo stesso nome (in caso stessi sostituendo l'immagine della casa)
                $files = glob($target_dir .$id_casa.'*');//array
                foreach ($files as $file) {
                    unlink($file);
                }
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    echo "L'immagine è stata caricata ";
                }
            } 

        }else{
            echo "Errore:si prega di caricare un immagine";    

        }}




    if(isset($_POST["eliminaFoto"])) {
        // Elimino la foto
        $target_dir = "immagini/";
        $files = glob($target_dir.$id_casa.'*');//array
        foreach ($files as $file) {
            unlink($file);
        }


    }


    echo '  <form action="mod_casa.php" method="post" enctype="multipart/form-data">';//form per caricare la foto
    echo "Seleziona una foto da caricare:";
    echo   " <input type='hidden' name='id_casa' value='$id_casa' >";//parametro che mi serve mantenere dopo aver ricaricato la pagina
    echo '<input type="file" name="fileToUpload" id="fileToUpload" required>
    <input type="submit" value="Carica foto" name="caricaFoto">
</form>   ';
    $immagine=glob('immagini/'.$id_casa.'.*');//uso la funzione glob al posto di if_exist perchè permette di mettere * al posto dell'estensione.Se restituisce qualcosa ha trovato l'immagine
    if($immagine != null){

        echo "Foto attuale:";
        echo "<img src='$immagine[0]'  width='120'
    height='120' id='image' style=' display: block;
    margin-left:0;'  > ";
        echo '  <form action="mod_casa.php" method="post" enctype="multipart/form-data">';//form per caricare la foto
        echo   " <input type='hidden' name='id_casa' value='$id_casa' >";//parametro che mi serve mantenere dopo aver ricaricato la pagina
        echo '<input type="submit" value="Elimina foto" name="eliminaFoto"></form>   ';
    }
    else{
        echo 'Attualmente non è presente alcuna foto';
    }



    //----------------------------FINE UPLOAD DELLA FOTO-------------------------

    echo "<br><a href='gest_case.php?pag=$pag'>Torna a gestione case</a>" 
    ?>




    </a>

</body>

<script>
    function myFunction(){ //funzione per visualizzare un div (con una select dentro)quando si seleziona "modifica"
        var e = document.getElementById("nuovo_ruolo");
        var b=document.getElementById("div_invisibile");
        var selezionato = e.options[e.selectedIndex].value;

        if(selezionato!="<?php echo $id_capo_famiglia ?>"){

            b.style.visibility="visible";
        }
        else{
            b.style.visibility="hidden";

        }

    }
</script>

</html>