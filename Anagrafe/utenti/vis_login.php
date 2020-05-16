<html>

    <?php
    /*
*** Autore:Ferraiuolo
*** Descrizione:vis_login.php
*** visualizzazione accessi al sistema
*/
    $config_path = __DIR__;
    $util = $config_path .'/../util.php';
    require $util;
    setup();
    isLogged("amministratore");
    ?>
    <html>
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

        <?php
        $util2 = $config_path .'/../db/db_conn.php';
        require_once $util2;
        ?>
        <?php stampaIntestazione(); ?>
        <body>
            <?php stampaNavbar(); ?>
            <?php 
            // Creo una variabile dove imposto il numero di record 
            // da mostrare in ogni pagina
            $x_pag = 10;

            // Recupero il numero di pagina corrente.
            // Generalmente si utilizza una querystring
            $pag = isset($_GET['pag']) ? $_GET['pag'] : 1;

            // Controllo se $pag è valorizzato e se è numerico
            // ...in caso contrario gli assegno valore 1
            if (!$pag || !is_numeric($pag)) $pag = 1;

            $filtro=""; //filtro vuoto

            if (isset($_SESSION['filtro_login_logs']) AND !isset($_POST['filtro'])) //se è salvato in sessione
                $filtro=$_SESSION['filtro_login_logs'];

            if (isset($_POST['filtro'])) //se è stato applicato un filtro
            {
                if($_POST['filtro']=="disattivato"){
                    $filtro="";
                    $_SESSION['filtro_login_logs']="";
                }
                if($_POST['filtro']=="riusciti"){
                    $filtro=" WHERE USER IS NOT NULL";
                    $_SESSION['filtro_login_logs']=" WHERE USER IS NOT NULL";
                }
                if($_POST['filtro']=="falliti"){
                    $filtro=" WHERE USER IS NULL";
                    $_SESSION['filtro_login_logs']=" WHERE USER IS NULL";
                }

            }  


            $query = "SELECT count(*) as cont FROM login_logs";
            $query .=$filtro;
            $result = $conn->query($query);
            $row = $result->fetch_array();
            $all_rows= $row['cont'];


            //  definisco il numero totale di pagine
            $all_pages = ceil($all_rows / $x_pag);

            // Calcolo da quale record iniziare
            $first = ($pag - 1) * $x_pag; 

            echo "<h2><center> <i class='fa fa-user'></i> Accessi effettuati nel sistema</center></h2>";
            //Select option per la scelta della zona
            echo "<form action='vis_login.php' method='POST'><br>";
            echo   "Visualizza : <select name='filtro'>";
            if($filtro=="")
                echo "<option value='disattivato' selected>Tutti </option>"; //opzione selected
            else
                echo "<option value='disattivato' >Tutti </option>";

            if($filtro==" WHERE USER IS NOT NULL")
                echo "<option value='riusciti' selected>Login riusciti</option>"; //opzione selected
            else
                echo "<option value='riusciti' >Login riusciti</option>";

            if($filtro==" WHERE USER IS NULL")
                echo "<option value='falliti' selected>Login falliti</option>"; //opzione selected
            else
                echo "<option value='falliti' >Login falliti</option>";
            echo "</select>";
            echo " <input type='submit' class='button' value='Conferma'>";
            echo " </form>";

            //query per l'elenco degli utenti
            $query = "SELECT USER,DATA,IP";
            $query .= " FROM login_logs";
            $query .=$filtro;
            $query .= " ORDER BY DATA DESC";
            $query .= " LIMIT $first, $x_pag";
            $result = $conn->query($query);

            if ($result->num_rows !=0)
            {
                echo "<table border>";
                echo "<tr>";
                echo "<th>Riuscito</th>";
                echo "<th>Utente</th>";
                echo "<th>Indirizzo IP</th>";
                echo "<th>Data accesso</th>";
                echo "</tr>";

                while ($row = $result->fetch_array())
                {

                    echo "<tr>";
                    if($row['USER']!=null){
                        echo "<td>SI</td>";
                    }
                    else{
                        echo "<td>NO</td>";
                    }
                    echo "<td>$row[USER]</td>";
                    echo "<td>$row[IP]</td>";
                    echo "<td>$row[DATA]</td>";

                }
                echo "</table>";
            }
            else
                echo " Nessun accesso al sistema &egrave; presente nel database.";
            echo "<br> Numero di accessi: $all_rows<br>";

            // visualizza pagine
			echo "<div class='pagi'><nav aria-label='...' > <ul class='pagination'>";
            $vis_pag = $config_path .'/../vis_pag.php';
            require $vis_pag;
		    echo "</div></ul></nav>";


            $result->free();
            $conn->close();	
            ?>   
        </body>
    </html>