<?php
/*
*** modifica_persona.php
*** 14/3/2020: A.Carlone:  correzioni varie
*** 2/03/2020  Gobbi Dennis Arneodo Alessandro: inserimento dei dati nelle tabelle storico
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
$pag=$_SESSION['pag_p']['pag_p'];
//unset($_SESSION['pag_p']);
?>
<?php stampaIntestazione(); ?>
<body>
    <?php stampaNavbar(); ?>
    <?php
    $id_pers = $_POST['id_pers'];
    //echo "id pers = ". $id_pers;
    $_SESSION["id_persona_modifica"]= $_POST['id_pers'];


    //$result = $conn->query("START TRANSACTION");
    $conn->begin_transaction();
    $query = "SELECT * FROM persone WHERE id=$id_pers FOR UPDATE";	// gestione concorrenza
    $result=$conn->query($query);
    if (!$result)
    {
        $msg_err = "Errore select for update";
        echo $conn->error;
    }

    //echo $query;

    $query = "SELECT p.nominativo, p.data_nascita, p.data_morte, pc.id_casa as id_casa,";
    $query .= " rpf.cod as cod_ruolo, rpf.descrizione as desc_ruolo,matricola_stud as matricola ";
    $query .=  " FROM  persone p  INNER JOIN  pers_casa pc  ON p.id=pc.id_pers ";
    $query .= " INNER JOIN  ruolo_pers_fam rpf  ON pc.cod_ruolo_pers_fam= rpf.cod ";
    $query .= " WHERE  p.id=$id_pers ";

    //echo $query;

    $result = $conn->query($query);
    $nr=$result->num_rows;
    if($nr==1)
    {
        $row = $result->fetch_array();
        $id_casa_mod = $row['id_casa'];
        $cod_ruolo_mod = $row['cod_ruolo'];
        $matricola=$row['matricola'];
        $nominativo = utf8_encode ($row['nominativo']);
        echo "<h3>Modifica persona: $nominativo  (id= $id_pers)</h3>";
        echo "<br>";
        echo "<form action='modifica_persona.php' name='form' id='form'  method='post'>";
        echo  " <label for='nominativo'>Nominativo :</label> <input type='text' name='nominativo' value ='". $nominativo."' required><br><br>";
        echo  " <label for='datan'>Data nascita : </label><input type='date' name='data_nascita' value = '".$row['data_nascita']."' required><br>";
        echo  " <label for='datam'>Data morte : </label><input type='date' name='data_morte' value = '".$row['data_morte']."'><br>";

        $query = "SELECT id, nome FROM casa c";
        //echo $query;

        $result = $conn->query($query);
        $nr=$result->num_rows;
        echo  "<label for='casa'>Residente nella casa:</label>";
        echo "<select name='id_casa_modifica' required>";
        for($i=0;$i<$nr;$i++)
        {
            $row = $result->fetch_array();
            if($id_casa_mod == $row["id"])
                echo "<option value='".$row["id"]."' selected>". $row["nome"]." </option>";
            else
                echo "<option value='".$row["id"]."'>".$row["nome"]." </option>";
        }
        echo "</select><br>";

        $query = "SELECT distinct cod, descrizione  FROM ruolo_pers_fam";
        $result = $conn->query($query);
        $nr=$result->num_rows;

        echo   "<label for='ruolo'>Ruolo nella famiglia:</label> ";
        echo "<select name='id_ruolo_modifica' required>";

        for($i=0;$i<$nr;$i++)
        {
            $row = $result->fetch_array();
            if($cod_ruolo_mod == $row["cod"])
                echo "<option value='".$row["cod"]."' selected>". $row["descrizione"]." </option>";
            else
                echo "<option value='".$row["cod"]."'>".$row["descrizione"]." </option>";
        }
        echo "</select><br>";
        
        if($matricola!=null && $matricola!=''){
            $query = "SELECT s.descrizione as descr,s.data_inizio_val,s.data_fine_val FROM studenti s where matricola='$matricola'";
            $result = $conn->query($query);
            
            $row = $result->fetch_array();
        }else{
            $matricola="";
            
        }
        $inizio_matricola="";
        $fine_matricola="";
        $desc_matricola="";

        if(isset ($row["s.data_inizio_val"])){
            $inizio_matricola=$row["s.data_inizio_val"];
        }
        if(isset ($row["s.data_fine_val"])){
            $fine_matricola=$row["s.data_fine_val"];
        }
        if(isset ($row["descr"])){
            $desc_matricola=$row["descr"];
        }
        echo  " <label for='matricola'>Matricola : </label><input type='text' name='matricola' placeholder='Modifica matricola se è uno studente' value = '".$matricola."'><br>";
        echo  " <label for='desc_matricola'>Descrizione matricola : </label><input type='text' name='desc_matricola' value = '".$desc_matricola."'><br>";
        echo  " <label for='inizio_matricola'>Data inizio matricola : </label><input type='date' name='inizio_matricola'  value = '".$inizio_matricola."'><br>";
        echo  " <label for='fine_matricola'>Data fine matricola : </label><input type='date' name='fine_matricola' value = '".$fine_matricola."'><br>";
        echo "<button type='submit' class='button'>Modifica</button>";
        echo "</form>";
        
    }
    else 
    {
        echo "mancano le specifiche per poterla modificare";
    }
    echo "<br><a href='gest_persone.php?pag=$pag'>Torna a gestione persone</a>" 
    ?>
</body>
</html>