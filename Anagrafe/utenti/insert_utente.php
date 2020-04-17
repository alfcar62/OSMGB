<html>

    <?php
    /*
*** ins_utente.php
*** 21/3/2020: Gobbi Dennis: Aggiunta funzione PwChecker() e tooltip()
*** 19/3/2020: A. Carlone. Corretta indentazione
*** Autore:Ferraiuolo
*** Descrizione:Form aggiunta di un utente
*** Data ultima modifica:22/03/2020  Modifica:aggiunta sale
*/ 
    $util1="../util.php";
    $util2="../db/db_conn.php";
    require_once $util2;
    require_once $util1;
    isLogged("gestore");
    setup();
    stampaIntestazione(); ?>

    <body>
        <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>



        <header><?php stampaNavbar(); ?></header>
        <?php
        ?>
        <!--<div class="container">-->
        <div>
            <h>Registrazione di un nuovo utente<br></h>
            <form id="login" action="insert_utente.php" method="POST">
                Username:
                <input type="text" name="user" required><br>
                Password:
                <input type="password" name="psw1" id="psw" required><span id="info"><img onmouseover="tooltip(event)" onmouseout="tooltip(event)" src="../img/infoIcon.png" style="height:25px;width:50px;"></span>
                <span id="error" style="visibility:hidden">La password deve avere almeno 8 caratteri e avere almeno un carattere maiuscolo,uno minuscolo,un numero e un carattere speciale!</span>
                <br>
                Conferma password:
                <input type="password" name="psw2" id="psw1" required><br>
                Tipo di utente:
                <select name="accesso">
                    <option value="admin">admin</option>
                    <option value="gestore">gestore</option>
                    <option value="utente">utente</option>
                </select><span id="info2"><img onmouseover="tooltip2(event)" onmouseout="tooltip2(event)" src="../img/infoIcon.png" style="height:25px;width:50px;"></span>
                <span id="error2" style="visibility:hidden">Admin: accesso completo |
                    Gestore: non può registrare nuovi utenti |
                    Utente: può visualizzare solo le statistiche</span><br>
                <input type="button" class="button" name="login" value="Aggiungi" id="log" onclick="PwChecker()">
                <?php
                if (isset($_POST['user']) && isset($_POST['psw1'])) 
                {    
                    $psw1=$_POST["psw1"];
                    $psw1=stripslashes($psw1);						//protezione da sql injection
                    $psw1=mysqli_real_escape_string($conn,$psw1);  

                    $psw2=$_POST["psw2"];
                    $psw2=stripslashes($psw2);						//protezione da sql injection
                    $psw2=mysqli_real_escape_string($conn,$psw2); 
                    $id_accesso=$_POST["accesso"];
                    if($psw1 !=$psw2) // controllo se sono diverse le psw
                        alert("le psw non corrispondono");
                    else
                    {
                        if(strlen ( $psw1 )<8){ //controllo se i caratteri sono almeno 8
                            alert("errore,la psw è troppo corta");
                        }
                        else
                        {        

                            $utente=$_POST['user'];
                            $bytes = my_random_bytes(10);
                            $sale=(bin2hex($bytes));
                            $codificata=hash('sha256',$psw1.$sale);   
                            // prepare 
                            $stmt = $conn->prepare("INSERT INTO utenti (user,password,DATA_INIZIO_VAL,id_accesso,sale) VALUES (?,?,?,?,?)");
                            $data=date('Y/m/d');
                            //bind
                            $stmt->bind_param("sssss",$utente,$codificata,$data,$id_accesso,$sale);
                            //execute
                            $r=$stmt->execute();
                            if($r)
                                alert("Utente registrato con successo");
                            else{
                                alert("Errore,nome utente già in uso");
                            } 
                        }
                    }
                }
                ?>
            </form>


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
                function tooltip2(event){
                    document.getElementById("error2").style.visibility="visible";
                    if(event.type=="mouseover"){
                        document.getElementById("error2").style.visibility="visible";
                    }
                    else if(event.type=="mouseout"){
                        document.getElementById("error2").style.visibility="hidden";
                    }
                }
            </script>
            <script>

                function PwChecker(){
                    var pw=document.getElementById("psw").value;
                    console.log(pw);
                    var pattern=new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})","g");
                    var isStrong=pattern.test(pw);
                    if(isStrong){
                        console.log("strong");
                        $("#login").submit();
                    }else alert("Password non valida!\nInserire una password di 8 caratteri con un carattere maiuscolo,minuscolo,un numero e un carattere speciale tra questi:'!' '@' '#' '\$' '%' '\^' '&' '\*' '\_'");
                }
            </script>




            </body>
        </div>
</html>
