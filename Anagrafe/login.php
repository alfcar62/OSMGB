<?php
//Autore:Ferraiuolo
//Descrizione:Login
//Data ultima modifica:25/02/2020  Modifica:aggiunta commenti,aggiunta bind ed execute per evitare sql injection
$util1="util.php";
$util2="db/db_conn.php";
require_once $util2;
require_once $util1;
setup();

$_SESSION['loggato'] = false;
if (isset($_POST['user']) && isset($_POST['psw']))
{
    $psw=$_POST["psw"];
    $utente=$_POST['user'];

    $utente = stripslashes($utente);				// protezione da SQL injection		
    $utente = mysqli_real_escape_string($conn,$utente);	// protezione da SQL injection	

    $psw = stripslashes($psw);						// protezione da SQL injection	
    $psw = mysqli_real_escape_string($conn,$psw);			// protezione da SQL injection	
   
    //tempi di delete dei record:
    $login_effettuato="1 MONTH";//modificare questa variabile per cambiare il tempo di delete dei record con login effettuato
    $login_fallito="1 DAY";//modificare questa variabile per cambiare il tempo di delete dei record con login fallito
        
    $query="DELETE FROM login_logs WHERE login_logs.DATA < NOW() - INTERVAL {$login_effettuato} AND USER is not null";
    $result=$conn->query($query);

    $query="DELETE FROM login_logs WHERE login_logs.DATA < NOW() - INTERVAL {$login_fallito} AND USER is null";
    $result=$conn->query($query);
    
    $ip = $_SERVER["REMOTE_ADDR"];//ip dell'utente 
    $timestamp=time();
    $data_ora = date("Y-m-d H:i:s");
    $accesso=true;//variabile temporanea
    if($ip=='::1')//::1 è in localhost
        $ip='127.0.0.1';
    
    $query="select count(*) as tentativi from login_logs where ip='$ip'";

    $result=$conn->query($query);
    if($result)
        $row= $result->fetch_array();

    if($row['tentativi']>2) //se ci sono almeno 2 tentativi dallo stesso ip
    {
        $query="select id,data from login_logs where ip='$ip' order by data desc limit 2,1";//ordina in modo desc e prende la terza riga
        $result=$conn->query($query);
        $row = $result->fetch_array();
        $cont=$result->num_rows;
        
        $primo_tentativo=strtotime($row["data"]);

        if(($timestamp-$primo_tentativo)<30)//se sono passati meno di 30s dall'ultimo record
         {
			$accesso=false;  
         } 
    }  

    if($accesso==true)//se non si è raggiunto il limite di tentativi
    {
        $stmt = $conn->prepare("SELECT * from utenti where user =?");
        //bind
        $stmt->bind_param("s",$utente);
        //execute
        $stmt->execute();
        $result = $stmt->get_result();
        if($result){
            $fin= $result->fetch_array();
            $stmt = $conn->prepare("SELECT * from utenti where user =? AND password=? ");
            //bind
            $codificata=hash('sha256',$psw.$fin['SALE']);  
            $stmt->bind_param("ss",$utente,$codificata);
            //execute
            $stmt->execute();
            $result = $stmt->get_result();
        }
        if($result){
            $fin= $result->fetch_array();
            $token=uniqid();
            $query="update utenti set token='{$token}' where user='{$fin["USER"]}'";
            $result2 = $conn->query($query);
        }
        if($fin)//se true l'accesso è andato a buon fine
        {
            $query_logs="insert into login_logs(ip,data,user) values ('$ip','$data_ora','$utente')";
            $result=$conn->query($query_logs);

            $_SESSION['login_time']=$timestamp;
            $_SESSION['loggato'] = true;
            $_SESSION['tipo']=$fin["ID_ACCESSO"];
            $_SESSION['nome']=$fin["USER"];
            $_SESSION['token']=$token;

            header("Location: index.php?welcome=true");   
        }
        else{//se i dati sono incorretti
            $query_logs="insert into login_logs(ip,data) values ('$ip','$data_ora')";
            $result=$conn->query($query_logs);

            echo "Username e/o password sbagliati";
        }
    }
    else
    {
        echo "<div id='troppiTentativi'>";
        echo "<p  style='color:red;'>ERRORE,TROPPI TENTATIVI DI ACCESSO DALLA STESSA POSIZIONE IN POCO TEMPO,SI PREGA DI ASPETTARE <span id='timer'>";
        echo 30-($timestamp-$primo_tentativo);
        echo "</span> SECONDI</div>";
    }
}//isset POST
?>


<script type="text/javascript">//script che cambia il contenuto del testo dentro  <span id='timer'> permettendo di simulare un countdown
    var tempo =document.getElementById("timer").textContent;
    var timer = setInterval(function()//setInterval per ripetere la funzione ogni 1000s(definiti a fine funzione)
                            {
        tempo--;
        document.getElementById("timer").textContent = tempo;
        if(tempo <= 0)
        {
            clearInterval(timer);
            document.getElementById("troppiTentativi").textContent = "Adesso è possibile riprovare ad accedere";
        }
    },1000);
</script>


<html>
    <?php stampaIntestazione(); ?>
    <body>
        <?php stampaNavbar(); ?>
        <?php
        if (!login())
        {
        ?>
        <!--<div class="container">-->

        <div align="center">    <h2>Accesso al Sistema</h2>
		<br><br>
        <form id="login" action="login.php" method="POST">
                Username: 
                <input type="text" name="user"><br>
                Password: 
                <input type="password" name="psw" required><br>
                <input type="submit" class="button" name="login" value="Login" required>
            </form></div>
        <?php
        }
        ?>
    </body>

</html>