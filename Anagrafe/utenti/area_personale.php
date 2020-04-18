<?php
/*
*** Autore:Ferraiuolo
*** Descrizione:Area personale dell'utente
*** 25/03/2020  Ferraiuolo:creazione del file
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
isLogged("gestore");
?>
<html>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <?php
    $util2 = $config_path .'/../db/db_conn.php';
    require_once $util2;
    ?>
    <?php stampaIntestazione(); ?>
    <body>
        <?php stampaNavbar(); ?>
        <?php 

        $query = "SELECT id_accesso,data_inizio_val";
        if(isset($_POST['cambiaPsw']))
            $query .=" ,password,sale";
        $query .=" from utenti where user='{$_SESSION['nome']}'";

        $result = $conn->query($query);
        $row = $result->fetch_array();

        echo "<h2> Area personale</h2>";
        echo "<br> NOME UTENTE: {$_SESSION['nome']} <br>";
        echo "<br> DATA CREAZIONE ACCOUNT: ";
        if(isset($row['data_inizio_val'])){
            echo "$row[data_inizio_val]";
        }
        else{
            echo "Non presente"; 
        }
        echo "<br>";
        echo "<br> TIPO DI ACCESSO : {$_SESSION['nome']} <br>";

        if(isset($_POST['formCambiaPsw']) || isset($_POST['cambiaPsw']) ){
        ?>
        <br><form action='area_personale.php' method='POST'>

        Password attuale:<input type='password'  name='pswOld' required><br>
        Nuova password: <input type='password'  name='pswNew1'id='pswNew1' required><span id='info'><img onmouseover='tooltip(event)' onmouseout='tooltip(event)' src='../img/infoIcon.png' style='height:25px;width:50px;'></span>
        <span id='error' style='visibility:hidden'>La password deve avere almeno 8 caratteri e avere almeno un carattere maiuscolo,uno minuscolo,un numero e un carattere speciale</span><br>

        Ripeti password: <input type='password'  name='pswNew2' required>
        <br><input type='submit' class='button' name='cambiaPsw' value='Cambia password' >
        </form>
        <?php 
        }else{
            if(!isset($_POST['cambiaPsw'])){
                echo " <form action='area_personale.php' method='POST'>";
                echo "<br><input type='submit' class='button' name='formCambiaPsw' value='Cambia password' ia>";
                echo"</form>";
            }
        }
        if(isset($_POST['cambiaPsw'])){
            $pswNew1=hash('sha256',$_POST['pswNew1'].$row['sale']);
            $pswNew2=hash('sha256',$_POST['pswNew2'].$row['sale']);
            $pswOld=hash('sha256',$_POST['pswOld'].$row['sale']);
            $pswAttuale=$row['password'];
            if($pswAttuale==$pswOld){
                if($pswNew1 ==$pswNew2) // controllo se sono diverse le psw
                {
                    if (preg_match('#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#',$_POST['pswNew1'])==1){        
                        $bytes = my_random_bytes(10);
                        $saleNuovo=(bin2hex($bytes));
                        $codificata=hash('sha256',$_POST['pswNew1'].$saleNuovo);   
                        // prepare 
                        $stmt = $conn->prepare("update utenti set password=?,sale=? where user=?");
                        //bind
                        $stmt->bind_param("sss",$codificata,$saleNuovo,$_SESSION['nome']);
                        //execute
                        $r=$stmt->execute();
                        if($r)
                            EchoMessage("Password cambiata con successo","area_personale.php");
                        else
                            alert("Errore nell'aggiornamento della password,si prega di riprovare");
                        
                    }
                    else
                        alert("Password non valida: inserire una password di 8 caratteri con un carattere maiuscolo,minuscolo,un numero e un carattere speciale!");   
                    
                }
                else
                    alert("Errore,le nuove password non corrispondono");

            }
            else
                alert("Errore,la password attuale è incorretta");
            
        }

        $result->free();
        $conn->close();	
        ?>  
        <script>

            function tooltip(event){
                document.getElementById("error").style.visibility="visible";
                if(event.type=="mouseover"){
                    document.getElementById("error").style.visibility="visible";
                }
                else if(event.type=="mouseout"){
                    document.getElementById("error").style.visibility="hidden";
                }
            }
        </script>

    </body>
</html>