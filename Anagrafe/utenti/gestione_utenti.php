<?php
/*
*** Autore:Ferraiuolo
*** Descrizione:Gestione utenti
*** 25/02/2020  Ferraiuolo:aggiunta tabella per visualizzare gli utenti
*** 22/03/2020  Ferraiuolo:aggiunta messaggio di conferma
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;
setup();
?>
<html>
    <link rel="stylesheet" type="text/css" href="../css/style.css">

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


        //Elimino un utente se è stata cliccata la x
        if (isset($_POST['idElimina']))
        {
            if (isset($_POST['si'])){

            if($_POST['idElimina']!=$_SESSION['nome'])
            {
                $query="DELETE FROM utenti WHERE user='{$_POST['idElimina']}'";
                $result=$conn->query($query);
                if($result)
                 
              ?> 
	 <script type="text/javascript">
     alert("Utente eliminato con successo");
   </script>
<?php
            }
            else {
        ?>
    <script type="text/javascript">
     alert("Impossibile eliminare l'utente attualmente in uso");
   </script>
        <?php
            }
        }}
        
        $query = "SELECT count(user) as cont FROM utenti";
        $result = $conn->query($query);
        $row = $result->fetch_array();
        $all_rows= $row['cont'];


        //  definisco il numero totale di pagine
        $all_pages = ceil($all_rows / $x_pag);

        // Calcolo da quale record iniziare
        $first = ($pag - 1) * $x_pag; 

        echo "<h3> Elenco Utenti che hanno accesso al sistema</h3>";
        $perm = $_SESSION['tipo'];
		if ($perm == "admin")
			$perm = "amministratore ";
		else if ($perm == "gestore")
			$perm = "gestore";
		else if ($perm == "utente")
			$perm = "utente generico";
        echo "<br> Utente collegato: {$_SESSION['nome']} - permesso: $perm <br>";
        echo "<a href='insert_utente.php'><br>";
        echo "Aggiungi nuovo utente </a><br><br>";

        if (isset($_POST['tipo']))
            if($_POST['tipo']!='tutti')
                $tipo = $_POST['tipo'];
        //Select option per la scelta dell tipo di utente
        echo "<form action='gestione_utenti.php' method='POST'><br>";
        echo   "Selezione permessi : <select name='tipo'>";
        $result = $conn->query("SELECT DISTINCT id_accesso from utenti");
        $nz=$result->num_rows;
        echo "<option value='tutti'>".Tutti."</option>";
        for($i=0;$i<$nz;$i++)
        {
            $row = $result->fetch_array();
            echo "<option value='".$row["id_accesso"]."'>".$row["id_accesso"]."</option>";
        }
        echo "</select>";
        echo " <input type='submit' class='button' value='Conferma'>";
        echo " </form>";

        //query per l'elenco degli utenti
        $query = "SELECT user,id_accesso,data_inizio_val";
        $query .= " FROM utenti";

        if (isset($tipo))//Condizione in caso si fosse scelta un solo tipo di utente
            if($_POST['tipo']!='tutti')
                $query .= " where id_accesso = '{$tipo}'";
        $query .= " ORDER BY user ASC";
        $query .= " LIMIT $first, $x_pag";
        $result = $conn->query($query);

        if ($result->num_rows !=0)
        {
            echo "<table border>";
            echo "<tr>";
            echo "<th>Utente</th>";
            echo "<th>permessi</th>";
            echo "<th>Data creazione</th>";
            echo "<th>Elimina </th>";
            echo "</tr>";

            while ($row = $result->fetch_array())
            {
                $perm = $row['id_accesso'];
				if ($perm == "admin")
					$perm = "amministratore (accesso completo)";
				else if ($perm == "gestore")
					$perm = "gestore (non può registrare nuovi utenti)";
				else if ($perm == "utente")
					$perm = "utente generico (può visualizzare solo le statistiche)";
                else 
					$perm = "sconosciuto";
                echo "<tr>";
                echo "<td>$row[user]</td>";
                echo "<td>$perm</td>";
                echo "<td>$row[data_inizio_val]</td>";
                echo " <form method='post' action='del_utente.php'>";
                echo "<th><button class='btn center-block' name='idElimina'  value='$row[user]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";
            }
            echo "</table>";
        }
        else
            echo " Nessun utente &egrave; presente nel database.";
        echo "<br> Numero di utenti: $all_rows<br>";

     	// visualizza pagine
        $vis_pag = $config_path .'/../vis_pag.php';
        require $vis_pag;

        $result->free();
        $conn->close();	
        ?>   
    </body>
</html>