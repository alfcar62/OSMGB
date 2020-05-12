<?php
/*
*** mod_persona.php
*** 14/3/2020: A.Carlone: correzioni varie, per la gestione dello storico
*** 03/03/2020  Autore:Gobbi Dennis
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
require_once $util1;
setup();
isLogged("gestore");
if (isset($_SESSION['pag_p']['pag_p']))
    $pag=$_SESSION['pag_p']['pag_p'];
unset($_SESSION['pag_c']);

// salvo i nuovi valori
$nominativo_new=$_POST['nominativo'];
//echo "nominativo nuovo:$nominativo_new<br>";

$id_ruolo_modifica_new=$_POST['id_ruolo_modifica'];//ruolo pers modifica
//echo "ruolo:$id_ruolo_modifica_new<br>";

$data_nascita_new=($_POST['data_nascita'] != '')? $_POST['data_nascita']:"0000-00-00";
//echo "nascita:$data_nascita_new<br>";

$data_morte_new=($_POST['data_morte'] != '') ? $_POST['data_morte']:"0000-00-00";
//echo "morte:$data_morte_new<br>";

$id_pers_modifica=$_SESSION['id_persona_modifica'];
//echo "id_pers:$id_pers_modifica<br>";

$id_casa_new=$_POST['id_casa_modifica'];//casa pers modifica
//echo "id_casa:$id_casa_new<br>";

$matricola_new=$_POST['matricola'];//matricola pers modifica

$inizio_matricola_new=$_POST['inizio_matricola'];//fine_matricola pers modifica

$fine_matricola_new=$_POST['fine_matricola'];//inizio_matricola pers modifica

try 
{
    // $conn->query("START TRANSACTION"); //inizio transazione

    //query per prendere i valori della persona pre-modifica

    $query  =  " SELECT p.nominativo,";
    $query .=  " p.data_nascita,";
    $query .=  " p.data_morte,";
    $query .=  " p.data_inizio_val,";
    $query .=  " p.sesso,";
    $query .=  " c.id as id_casa,";
    $query .=  " c.nome as nome_casa,";
    $query .=  " pc.cod_ruolo_pers_fam as cod_ruolo,";
    $query .=  " rpf.descrizione as desc_ruolo,";
    $query .=  " p.matricola_stud as matricola,";
    $query .=  " s.data_inizio_val as inizio_matricola,";
    $query .=  " s.data_fine_val as fine_matricola";
    $query .=  " FROM persone p LEFT JOIN studenti s ON s.matricola=p.matricola_stud, pers_casa pc, casa c, ruolo_pers_fam rpf";
    $query .= " WHERE p.id =$id_pers_modifica";
    $query .= " AND pc.id_pers = p.id";
    $query .= " AND c.id = pc.id_casa";
    $query .= " AND pc.cod_ruolo_pers_fam = rpf.cod";

    // echo "q1 ".$query."<br>";
    $result=$conn->query($query);
    if (!$result)
    {
        $msg_err = "Errore select n.1";
        throw new Exception($conn->error);
    }
    $row=$result->fetch_array();

    $tipo_operazione="MOD (";
    $casa_cambiata=false;
    $ruolo_cambiato=false;
    $nominativo_cambiato = false;
    $data_nascita_cambiata = false;
    $data_morte_cambiata = false;
    $matricola_cambiata=false;
    $nuova_matricola=false; //indica se la matricola precedentemente all'update non era presente.Utile per l'update della persona più avanti
    $inizio_matricola_cambiato=false;
    $fine_matricola_cambiato=false;

    $sesso_old =$row['sesso']; 
    $nome_casa_old =$row['nome_casa']; 
    $desc_ruolo_old =$row['desc_ruolo']; 
    $nominativo_old =$row['nominativo'];
    $cod_ruolo_old = $row['cod_ruolo']; 
    $data_nascita_old =  $row['data_nascita'];
    $data_morte_old =  $row['data_morte'];
    $id_casa_old = $row['id_casa'];
    $matricola_old=$row['matricola'];
    $inizio_matricola_old=$row['inizio_matricola'];
    $fine_matricola_old=$row['fine_matricola'];
    if($nominativo_new != $row['nominativo'])
    {
        $tipo_operazione.="-nominativo-";
        $nominativo_cambiato=true; 
    }

    if($id_ruolo_modifica_new != $row['cod_ruolo'])
    {
        $tipo_operazione.="-ruolo-";
        $ruolo_cambiato=true;
        	//echo $row['cod_ruolo'];
       // 	echo "ruolo cambiato";
    }

    $data_nascita =($row['data_nascita'] != '') ? $row['data_nascita']:"0000-00-00";

    if($data_nascita_new != $data_nascita)
    {
        $tipo_operazione.="-data_nascita-";
        $data_nascita_cambiata=true;
    }

    $data_morte =($row['data_morte'] != '') ? $row['data_morte']:"0000-00-00";

    if($data_morte_new != $data_morte)
    {
        $tipo_operazione.="-data_morte-";
        $data_morte_cambiata=true;
    }


    if( $id_casa_new != 's' && $id_casa_new != $row['id_casa'])
    {
        $tipo_operazione.="-casa-";
        $casa_cambiata=true;
    }
    if($matricola_new != $row['matricola'])
    {
        $tipo_operazione.="-matricola-";
        $matricola_cambiata=true; 
    }
    if($inizio_matricola_new != $row['inizio_matricola'] && $inizio_matricola_new !="")
    {
        
        $tipo_operazione.="-inizio_matricola-";
        $inizio_matricola_cambiato=true; 
    }
    if($fine_matricola_new != $row['fine_matricola'] && $fine_matricola_new !="")
    {
        $tipo_operazione.="-fine_matricola-";
        $fine_matricola_cambiato=true; 
    }


    $tipo_operazione.=")";

    $data_inizio_val=$row['data_inizio_val'];
    $currentdate=date('Y/m/d');


    // se la nuova casa ha gi� un capo famiglia, non posso scegliere come ruolo capo famiglia
    if ($ruolo_cambiato  && $id_ruolo_modifica_new == 'CF')
    {
        $query  =  " SELECT count(pc.id) as cont FROM casa c, pers_casa pc ";
        $query .=  " WHERE pc.id_casa = c.id ";
        $query .=  " AND c.id =". $id_casa_new;
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

    /*
*** INSERT su persone_sto (vecchi valori)
*/
    $query="INSERT INTO persone_sto (";
    $query .= "  TIPO_OP, ";
    $query .= "  ID_PERSONA, ";
    $query .= "  NOMINATIVO, ";
    $query .= "  SESSO, ";
    $query .= "  DATA_NASCITA, DATA_MORTE,";
    $query .= "  ID_CASA, NOME_CASA,";
    $query .= "  COD_RUOLO_PERS_FAM, DESC_RUOLO_PERS_FAM,";
    $query .= "  DATA_INIZIO_VAL,MATRICOLA_STUD,DATA_FINE_VAL ";
    $query .= "  )";
    $query .= " VALUES(";
    $query .= "'$tipo_operazione',";
    $query .= "'$id_pers_modifica',";
    $query .= "'$nominativo_old',";
    $query .= "'$sesso_old',";

    if ($data_nascita_new == "0000-00-00")
        $query .= "NULL,";
    else
        $query .= "'".$data_nascita_new."',";

    if ($data_morte_new == "0000-00-00")
        $query .= "NULL,";
    else
        $query .= "'".$data_morte_new."',";

    $query .= $id_casa_old .",";
    $query .= "'".$nome_casa_old."',";
    $query .= "'".$cod_ruolo_old."',";
    $query .= "'".$desc_ruolo_old."',";
    $query .= "'$data_inizio_val',";
    if($matricola_old!=null and $matricola_old!='')
    $query .= "'$matricola_old',";
    else
        $query .= "NULL,";
    $query .= "'$currentdate'";
    $query .= ")";

    // echo "q2 ".$query."<br>";
    $result = $conn->query($query);

    if (!$result)
    {
        $msg_err = "Errore insert persone_sto".$query;
        throw new Exception($conn->error);
    }

    $upd_pers = false;
    $upd_pers_casa = false;
    $upd_matricola=false;

    if ($nominativo_cambiato || $data_nascita_cambiata || $data_morte_cambiata)
        $upd_pers=true;

    if($matricola_cambiata || $inizio_matricola_cambiato || $fine_matricola_cambiato)
        $upd_matricola=true;
    
    if ($casa_cambiata || $ruolo_cambiato)
        $upd_pers_casa = true;

if($upd_matricola){
if($matricola_cambiata and ($matricola_new!=null or $matricola_new!='')){//verifico se la matricola è già esistente in quanto deve essere univoca
        $query  =  " SELECT count(s.matricola) as count from studenti s "; 
        $query .=  " WHERE s.matricola ='$matricola_new'";
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
}


        if($matricola_old==null || $matricola_old==""){ //se la matricola è da inserire e non da modificare
            $query= "INSERT INTO studenti(matricola,data_inizio_val,data_fine_val) ";
            $query.="VALUES('$matricola_new'";
            if ($inizio_matricola_new == "0000-00-00")
                $query .= ",'NULL'";
            else
                $query .=",'$inizio_matricola_new'";
            if ($fine_matricola_new == "0000-00-00")
                $query .= ",'NULL'";
            else
                $query .=",'$fine_matricola_new')";

            $result = $conn->query($query);
            if (!$result)
            {
                $msg_err = "Errore insert studenti";
                throw new Exception($conn->error);
            }
            $nuova_matricola=true;//variabile che indica che deve essere esserci anche la matricola nell'update
        }else{// se c'è da fare un update o delete
            if($matricola_new==null or $matricola_new==''){//se è da eliminare la matricola
                $query ="DELETE from studenti where matricola='{$matricola_old}'";
                $result = $conn->query($query);
                if (!$result)
                {
                    $msg_err = "Errore delete studenti";
                    throw new Exception($conn->error);
                }
            }else{//se è da modificare la matricola
                $query= "UPDATE studenti SET ";
                $query.="matricola="."'".$matricola_new."'";
                $query.=",data_inizio_val="."'".$inizio_matricola_new."'";
                $query.=",data_fine_val="."'".$fine_matricola_new."'";
                $query .= " where matricola= '$matricola_old'";
                $result = $conn->query($query);
                if (!$result)
                {
                    $msg_err = "Errore update studenti";
                    throw new Exception($conn->error);
                }
            }
        }

    }
    
    if($upd_pers || $nuova_matricola)//$nuova_matricola è settata a true in caso si dovesse fare l'update su persone(solo quando c'è una nuova matricola perchè negli altri casi grazie all' ON CASCADE UPDATE si aggiorna da sola) 
    {
        /*
    *** UPDATE persone
    */

        $query= "UPDATE persone SET ";
        $query .= "nominativo="."'".$nominativo_new."'";

        if ($data_nascita_new == "0000-00-00")
            $query .= ",data_nascita = NULL ";
        else
            $query .= ",data_nascita = '". $data_nascita_new . "'";

        if ($data_morte_new == "0000-00-00")
            $query .= ",data_morte = NULL ";
        else
            $query .= ",data_morte= '". $data_morte_new . "' ";
        if($nuova_matricola)
            $query .= ",matricola_stud= '". $matricola_new . "' ";
        $query .= " where id= ".$id_pers_modifica;

        echo "q3 ".$query."<br>";

        $result = $conn->query($query);
        if (!$result)
        {
            $msg_err = "Errore update persone";
            throw new Exception($conn->error);
        }
    }
    /*
    *** UPDATE pers_casa
    */
    if ($upd_pers_casa)
    {
        $query="UPDATE pers_casa ";
        $query=$query." SET cod_ruolo_pers_fam="."'".$id_ruolo_modifica_new. "'";
        $query=$query.", id_casa=".$id_casa_new;
        $query=$query." WHERE pers_casa.id_pers=".$id_pers_modifica;

        //      echo "q4 ".$query."<br>";;
        $result = $conn->query($query);
        if (!$result)
        {
            $msg_err = "Errore update pers_casa";
            throw new Exception($conn->error);
        }
    }
    $conn->commit();
    $conn->autocommit(TRUE);
    $conn->close();
}//try
catch ( Exception $e )
{
    $conn->rollback(); 
    $conn->autocommit(TRUE); // i.e., end transaction
    //	echo $conn->error; 
    $conn->close();
    //	echo $msg_err;
    echo "Errore nella modifica della persona";
    echo "transazione con rollback";
    $mymsg = "Modifica persona id=$id_pers_modifica " . $msg_err;
    EchoMessage($mymsg, "gest_persone.php?pag=$pag");
}
EchoMessage("Modifica persona id=$id_pers_modifica effettuata correttamente", "gest_persone.php?pag=$pag");
?>




