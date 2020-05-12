<?php 
/*
***14/3/2020: A.Carlone: correzioni varie, aggiunta gestione transazione
***1/03/2020  Gobbi Dennis Alessandro Arneodo: bug fox su insert
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
$nominativo=$_POST["nome_persona"];
$data_nascita=$_POST["data_nascita"];
$id_casa=$_POST["id_casa_nuova"];
$cod_ruolo=$_POST["id_ruolo_nuovo"];
$sesso=$_POST["sesso"];
$matricola=$_POST['matricola'];
$inizio_matricola=$_POST['inizio_matricola'];
$data_odierna = date("y/m/d");

try 
{
    $conn->query("START TRANSACTION"); //inizio transazione

    // se la  casa ha già un capo famiglia, non posso scegliere come ruolo capo famiglia
    if ($cod_ruolo == 'CF')
    {
        $query  =  " SELECT count(pc.id) as cont FROM casa c, pers_casa pc ";
        $query .=  " WHERE pc.id_casa = c.id ";
        $query .=  " AND c.id =". $id_casa;
        $query .=  " AND pc.cod_ruolo_pers_fam = 'CF'";
        //	echo $query;

        $result = $conn->query($query);

        if (!$result)
        {
            $msg_err = "Errore select n.2";
            throw new Exception($conn->error);
        }
        $row = $result->fetch_array();
        if ($row['cont']>0) 
        {
            $msg_err = "Esiste un capo famiglia: selezionare altro ruolo";
            throw new Exception($msg_err);
        }
    }


    $query="select  max(id) as max_id_pers from persone";
    $result=$conn->query($query);
    $row=$result->fetch_array();
    $id_pers=$row["max_id_pers"]+1;
    $result->free();
    if(isset($matricola) and $matricola!=null){

        $query  =  " SELECT count(s.matricola) as count from studenti s "; //verifico se la matricola è già esistente in quanto deve essere univoca,se si lancio un exception
        $query .=  " WHERE s.matricola ='$matricola'";
        //	echo $query;

        $result = $conn->query($query);


        if (!$result)
        {
            $msg_err = "Errore count matricola";
            throw new Exception($conn->error);
        }
        $row = $result->fetch_array();
        
        if ($row['count']>0) 
        {
            $msg_err = "Esiste già un altra persona con la stessa matricola: verificarne la correttezza";
            throw new Exception($msg_err);
        }
        
        $query="INSERT INTO studenti (matricola,data_inizio_val) values ('$matricola','$inizio_matricola')"; 
        $result = $conn->query($query);
        // echo $query."<br>";
        if (!$result)
        {
            $msg_err = "Errore insert studenti".$query;
            throw new Exception($conn->error);
        }

        $query="INSERT INTO persone (id, nominativo, sesso, data_nascita,data_inizio_val,matricola_stud) values ($id_pers,'$nominativo','$sesso','$data_nascita','$data_odierna','$matricola')";
        $result = $conn->query($query);
        // echo $query."<br>";
        if (!$result)
        {
            $msg_err = "Errore insert persone";
            throw new Exception($conn->error);
        }


    }

    else{
        $query="INSERT INTO persone (id, nominativo, sesso, data_nascita,data_inizio_val) values ($id_pers,'$nominativo','$sesso','$data_nascita','$data_odierna')";
        $result = $conn->query($query);
        // echo $query."<br>";
        if (!$result)
        {
            $msg_err = "Errore insert persone";
            throw new Exception($conn->error);
        } 

    }

    $query2="INSERT INTO pers_casa (id_pers, id_casa, cod_ruolo_pers_fam, data_inizio_val)";
    $query2 .= " VALUES ($id_pers, $id_casa, '$cod_ruolo', '$data_odierna')";
    $result = $conn->query($query2);
    // echo $query2."<br>";
    if (!$result)
    {
        $msg_err = "Errore insert pers_casa";
        throw new Exception($conn->error);
    }
    $conn->commit(); 
    $conn->autocommit(TRUE); // end transaction
    $conn->close();
}
catch (Exception $e )
{
    $conn->rollback(); 
    $conn->autocommit(TRUE);	// end transaction
    $conn->close();
    //     echo "Errore in inserimento della persona";
    //	 echo $conn->error; 
    //	 echo "transazione con rollback";
    $mymsg = "Errore inserimento persona id=$id_pers " . $msg_err;
    EchoMessage($mymsg, "gest_persone.php");
}
EchoMessage("Inserimento  persona id=$id_pers effettuato correttamente", "gest_persone.php");
?>
