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

    $codificata=hash('sha256',$psw);				// codifica sha256
    // manca salt

    $ip = $_SERVER["REMOTE_ADDR"];



    $timestamp=time();
    if($ip=='::1')

        $query="select count(*) from login_logs where ip='$ip'";

    $result=$conn->query($query);
    if($result)
        $row= $result->fetch_array();

    if($row[0]>0) 
    {
        $c=0;//variabile temporanea
        $query="select id,ultimo_tentativo from login_logs where ip='$ip'";
        $result=$conn->query($query);
        while ($row = $result->fetch_array()) //per ogni volta che è stato effettuato un tentativo dallo stesso ip
        {
            if(($timestamp-$row["ultimo_tentativo"])>20)//se sono passati 20 secondi elimina il tentativo dai log
            {
                $query2="delete from login_logs where id='$row[id]'";
                $result2=$conn->query($query2); 
            }
            else
            {//altrimenti incrementa la variabile temporanea
                $c++;
                if(isset($ultimo_tentativo))
                {
                    if($row["ultimo_tentativo"]<$ultimo_tentativo)//assegno a $ultimo_tentativo il valore più vecchio
                        $ultimo_tentativo=$row["ultimo_tentativo"];
                }
                else
                {
                    if($c>3)
                        $ultimo_tentativo=$row["ultimo_tentativo"];//impostando la variabile $ultimo_tentativo indico che si è raggiunto il limite massimo di tentativi nel tempo prestabilito
                }
            }
        } //while    
    }  // row[0]>0
    if($result)
        $fin= $result->fetch_array();
    if(!isset($ultimo_tentativo))//se non si è raggiunto il limite di tentativi
    {
        $query="insert into login_logs(ip,ultimo_tentativo) values ('$ip','$timestamp')";
        $result=$conn->query($query);
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

            $_SESSION['login_time']=$timestamp;
            $_SESSION['loggato'] = true;
            $_SESSION['tipo']=$fin["ID_ACCESSO"];
            $_SESSION['nome']=$fin["USER"];
            $_SESSION['token']=$token;
            
            header("Location: index.php?welcome=true");   
        }
        else{

            echo "Username e/o password sbagliati";
        }
    }
    else
    {
        echo "<div id='troppiTentativi'>";
        echo "<p  style='color:red;'>ERRORE,TROPPI TENTATIVI DI ACCESSO DALLA STESSA POSIZIONE IN POCO TEMPO,SI PREGA DI ASPETTARE <span id='timer'>";
        echo 20-($timestamp-$ultimo_tentativo);
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
       <div class="dnav"  >    <?php stampaNavbar(); ?></div>
        <div class="pg">
        <?php
        if (!login())
        {
        ?>
        <!--<div class="container">-->
        <div  id="uspw">    <h>Login<br></h>
             <form id="login" action="login.php" method="POST">
                <input class="camp" id="user" type="text" name="user" placeholder="User"    >
                <input class="camp" id="psw" type="password" name="psw" placeholder="Password" required  ><br>
                <input type="submit" class="camp" id="butto" name="login" value="Login" required>
            </form></div>
        <?php
        }
            ?></div>

    </body>

</html>