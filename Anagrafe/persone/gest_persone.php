<?php
/*
*** Gest_persone.php: Gestione delle persone
*** Attivato da menu principale alla voce "persone"
*** Input:
*** se  POST(id_casa) valorizzato -> (arriva da gestione_case.php: 
***                si vuole l'elenco delle persone di una casa specifica
*** se  POST(cod_zona) valorizzato -> (arriva da qui per la scelta della zona)
***                si vuole l'elenco delle persone di una casa specifica
*** Output. Può richiamare:
*** mod_persona.php  (caso di modifica persona)
*** del_persona.php  (caso di cancellazione persona)
*** mostra_casa.php  (caso di motra dati della casa della persona)
*** vis_persona_sto.php  (caso di visualizzazione storico della persona)
***  9/4/2020: A.Carlone: visualizzazione deceduti
*** 15/3/2020: A.Carlone: migliorata gestione zone e ordinamento su id e nome moranca
*** 27/02/20 : Gobbi: Implementazione della gestione multilingue
*** 2/2/2020: A. Carlone: prima implementazione
*/
$config_path = __DIR__;
$util1 = $config_path .'/../util.php';
require_once $util1;
setup();
unsetPag(basename(__FILE__)); 
$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
?>

<html>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.search-box input[type="text"]').on("keyup input", function(){
                /* Get input value on change */
                var inputVal = $(this).val();
                var resultDropdown = $(this).siblings(".result");
                if(inputVal.length){
                    $.get("cerca_persona.php", {term: inputVal}).done(function(data){
                        // Display the returned data in browser
                        resultDropdown.html(data);
                    });
                } else{
                    resultDropdown.empty();
                }
            });

            // Set search input value on click of result item
            $(document).on("click", ".result p", function(){
                $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
                $(this).parent(".result").empty();
            });
        });
    </script>


    <?php
    $util2 = $config_path .'/../db/db_conn.php';
    require_once $util2;
    ?>
    <?php stampaIntestazione(); ?>
    <body>
    <?php stampaNavbar(); ?>
        <div class="search-box">
            <input type="text" autocomplete="off" placeholder="<?php echo $jsonObj->{$lang."Persone"}[1];?>..." /><!--Ricerca persone -->
            <div class="result"></div>
        </div>

        <?php

/*
*** 15/3/2020: Se viene richiamato da gest_case.php (mostra persone della casa) 
*/
        // vedo se arriva da gest_casa.php o da  menu persone ";
        if (isset($_POST['id_casa']))
        {
            $id_casa = $_POST['id_casa']; 
            $_SESSION['id_casa'] = $id_casa;
        }  
        else 
            $_SESSION['id_casa'] = 'tutte';

        if( isset($_SESSION['id_casa']) &&  ($_SESSION['id_casa'] != 'tutte'))		
            $id_casa =  $_SESSION['id_casa']; 

        // modificato per la gestione corretta della paginazione (A.C. 10/3/2020)
        // se $_POST['cod_zona'] valorizzato --> arriva  dall'action form
        // se $_SESSION  valorizzato --> arriva  dal $SERVER[PHP_SELF]
        if (isset($_POST['cod_zona']))
        {
            $cod_zona = $_POST['cod_zona']; 
            $_SESSION['cod_zona'] = $cod_zona;
        }  
        else 
        {
            if( isset($_SESSION['cod_zona']) &&  ($_SESSION['cod_zona'] != 'tutte'))		
                $cod_zona =  $_SESSION['cod_zona'];
        } 

/*
***    9/4/2020: A. Carlone: correzione problema paginazione per cambio selezione
*/
       if (isset($_SESSION['decessi'])) 
           $_SESSION['old_decessi'] =  $_SESSION['decessi'];

       if (isset($_POST['decessi']))		// arriva dal form stesso
        {
            $decessi = $_POST['decessi'];
            $_SESSION['decessi'] = $decessi;
        }  
        else 
        {
            if( isset($_SESSION['decessi']) &&  ($_SESSION['decessi'] != 'tutti'))		
                $decessi =  $_SESSION['decessi'];
			else
                $decessi = 'no'; 
        } 

        $x_pag = 10;
        // Recupero il numero di pagina corrente.

        $pag=Paginazione("pag_p");

        // Controllo se $pag ? valorizzato e se ? numerico
        // ...in caso contrario gli assegno valore 1
        if (!$pag || !is_numeric($pag)) $pag = 1; 


        $query2 = "SELECT count(p.id) as cont FROM persone p";
        $query2 .= " inner join pers_casa pc on pc.id_pers = p.id ";
        $query2 .= " inner join casa c       on pc.id_casa = c.id";
        $query2 .= " inner join morance m    on c.id_moranca = m.id";
        $query2 .= " inner join ruolo_pers_fam rpf ON  pc.cod_ruolo_pers_fam = rpf.cod ";

        if (isset($cod_zona) && ($cod_zona != 'tutte'))
            $query2 .= " inner join zone z on m.cod_zona = z.cod";

        $query2 .= " WHERE p.data_fine_val IS  NULL";

        if (isset($id_casa) && ($id_casa != 'tutte'))
        {  
            $query2 .= " AND c.id = $id_casa"; 
        }

        if (isset($cod_zona) && ($cod_zona != 'tutte'))
        {  
            $query2 .= " AND z.cod = '$cod_zona'";
        }

       if (isset($decessi) && ($decessi == 'si'))
            $query2 .= " AND p.data_morte IS NOT NULL";
       if (isset($decessi) && ($decessi == 'no'))
            $query2 .= " AND p.data_morte IS  NULL";

//       echo $query2;

        $result = $conn->query($query2);
        $row = $result->fetch_array();
        //esiste la count
        $all_rows= $row['cont'];


        //  definisco il numero totale di pagine
        $all_pages = ceil($all_rows / $x_pag);
        // Calcolo da quale record iniziare

         $first = ($pag - 1) * $x_pag;

		// se è cambiato qualcosa riparto dalla prima pagina

		if (isset($_SESSION['decessi']) &&
		    isset($_SESSION['old_decessi']))
			{
		     if ($_SESSION['decessi'] != $_SESSION['old_decessi'])
				 $first = 0;
			}

        echo "<h2> Villaggio di NTchangue: Elenco persone</h2>";

        echo "<a href='ins_persona.php'>".$jsonObj->{$lang."Persone"}[2]."</a><br><br>";//Aggiungi una nuova persona 
       
		echo"<a href='export_persone.php'>Export su excel</a><br><br>";

		echo "<a href='vis_sto_tot_persone.php'>";
        echo "Storia delle persone </a><br><br>";

        if (isset($_POST['cod_zona']))
            $cod_zona = $_POST['cod_zona'];
   
        //Select option per la scelta della zona
        echo "<form action='gest_persone.php' method='POST'><br>";
        echo   $jsonObj->{$lang."Morance"}[22].": <select name='cod_zona'>";//Selezione zona
        $result = $conn->query("SELECT * FROM zone");
        $nz=$result->num_rows;

        echo "<option value='tutte'> tutte </option>";//Tutte
        for($i=0;$i<$nz;$i++)
        {
            $row = $result->fetch_array();

            if(isset($cod_zona) && $cod_zona == $row["COD"])
                echo "<option value='".$row["COD"]."' selected>". $row["NOME"]." </option>";
            else
                echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
        }
        echo "</select>";
        echo " <input type='submit' class='button' value='Conferma'>";//conferma
        echo " </form>";

		//Select option per la scelta visualizzazione decessi
        echo "<form action='gest_persone.php' method='POST'><br>";
        echo   "Visualizza: <select  name='decessi'>";    
        echo "<option value='tutti'";
		if(isset($decessi) && ($decessi == 'tutti'))
		    echo " selected"; 
		echo "> tutti </option>";
	    echo "<option value='no'";
		if(isset($decessi) && ($decessi == 'no'))
		    echo " selected"; 
		echo "> viventi </option>";

	    echo "<option value='si'";
		if(isset($decessi) && ($decessi == 'si'))
		    echo " selected"; 
		echo "> deceduti </option>";    
        echo "</select>";
        echo " <input type='submit' class='button' value='Conferma'>";//conferma
        echo " </form>";

        // ordinamento su campi (11/3/2020) A.C.
        if (!isset($_GET['ord'])) 
        {
            $campo = 'id';  
            $ord = 'ASC';	// ordinamento ascendente
        }
        else
        { 
            $campo = $_GET['campo'];
            if ($_GET['ord'] == 'ASC')
                $ord = 'DESC';
            else
                $ord = 'ASC';
        }
        // ordinamento

        $result->free();

        $query = "SELECT ";
        $query .= " p.id, p.nominativo, p.sesso, p.data_nascita, p.data_morte,";
        $query .= " c.id as id_casa, c.id_moranca,c.nome nome_casa, m.nome nome_moranca,";
        $query .= " m.cod_zona,  c.id_casa_moranca, c.id_osm, ";
        $query .= " pc.cod_ruolo_pers_fam, rpf.descrizione,";
        $query .= " p.data_inizio_val, p.data_fine_val ";
        $query .= " FROM persone p";
        $query .= " INNER JOIN pers_casa pc ON  pc.id_pers = p.id";
        $query .= " INNER JOIN casa c ON  pc.id_casa = c.id";
        $query .= " INNER JOIN morance m ON  c.id_moranca = m.id";
        $query .= " INNER JOIN ruolo_pers_fam rpf ON  pc.cod_ruolo_pers_fam = rpf.cod ";
        $query .= " WHERE p.data_fine_val IS  NULL";
        if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '$cod_zona'"; 
        if (isset($id_casa)&& ($id_casa !='tutte'))
            $query .= " AND id_casa = $id_casa";
		if (isset($decessi) && ($decessi == 'si'))
            $query .= " AND p.data_morte IS NOT NULL";
        if (isset($decessi) && ($decessi == 'no'))
            $query .= " AND p.data_morte IS  NULL";
        $query .= " ORDER BY $campo " . $ord ;
        $query .= " LIMIT $first, $x_pag";

   //     echo $query;

        $result = $conn->query($query);

        $nr = $result->num_rows;
    
        if ($nr != 0)
        {
            echo "<table border>";
            echo "<tr>";  

            //echo "<th>id</th>";

            //id con ordinamento
            echo "<th>";
            echo "id <a href=\"".$_SERVER['PHP_SELF']."?ord=".$ord. "&campo=p.id"."\"> <IMG SRC='../img/freccia.png'  ALT='ordina'></th>";

            //	echo "<th>nominativo</th>";
            //nominativo con ordinamento
            echo "<th>";
            echo "nominativo <a href=\"".$_SERVER['PHP_SELF']."?ord=".$ord. "&campo=p.nominativo"."\"> <IMG SRC='../img/freccia.png'  ALT='ordina'></th>";

            echo "<th>".$jsonObj->{$lang."Persone"}[3]."</th>";//Sesso		
            echo "<th>".$jsonObj->{$lang."Persone"}[4]."</th>";//Data Nascita
            echo "<th>".$jsonObj->{$lang."Persone"}[5]."</th>";//Età
            echo "<th>Data Morte</th>";//Data Morte
            echo "<th>".$jsonObj->{$lang."Persone"}[6]."</th>";//Ruolo in famiglia
            echo "<th>moranca</th>";//Morança
            echo "<th>".$jsonObj->{$lang."Persone"}[7]."</th>";//Casa
            echo "<th>su OpenStreetMap</th>";
            echo "<th> data inizio val";//Data val
            echo "<th>".$jsonObj->{$lang."Morance"}[9]."</th>";//Modifica
            echo "<th>".$jsonObj->{$lang."Morance"}[10]."</th>";//Elimina   
            echo "<th>".$jsonObj->{$lang."Persone"}[7]."</th>";//Casa  
            echo "<th>".$jsonObj->{$lang."Morance"}[12]."</th>";//Storico
            echo "</tr>";

            echo "<tr>";
            while ($row = $result->fetch_array())
            { 
                echo "<td>$row[id]</td>";
                $mystr = utf8_encode ($row['nominativo']) ;
                echo "<td>$mystr</td>";
                echo "<td>$row[sesso]</td>";
                echo "<td>$row[data_nascita]</td>";
                echo "<td>".date_diff(date_create($row['data_nascita']), date_create('today'))->y."</td>";
                echo "<td>".$row['data_morte']."</td>";
                echo "<td>$row[descrizione] ($row[cod_ruolo_pers_fam])</td>";
                $mystr = utf8_encode ($row['nome_moranca']) ;
                echo "<td>$mystr</td>";
                echo "<td>$row[nome_casa]</td>";

                $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
                if ($row['id_osm'] != null && $row['id_osm'] != "0")
                { 
                    echo "<td>$row[id_osm]<a href=$osm_link target=new><i class='fa fa-map-marker'></i></a></td>"; 
                }
                else
                { 
                    echo "<td>&nbsp;</td>";
                }
                echo "<td>$row[data_inizio_val]</td>";

                echo " <form method='post' action='mod_persona.php'>";
                echo "<th><button class='btn center-block' name='id_pers'  value='$row[id]' type='submit';'><i class='fa fa-wrench'></i> </button> ". "</th></form>";

                echo " <form method='post' action='del_persona.php'>";
                echo "<th><button class='btn center-block' name='id_pers'  value='$row[id]' type='submit';'><i class='fa fa-trash'></i> </button> ". "</th></form>";	

                echo " <form method='post' action='mostra_casa.php'>";
                echo "<th><button class='btn center-block' name='id_persona'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i> </button> ". "</th></form>";

                echo " <form method='post' action='vis_persona_sto.php'>";
                echo "<th><button class='btn center-block' name='id_persona'  value='$row[id]' type='submit';'><i class='fa fa-eye'></i> </button> ". "</th></form>";
                echo "</tr>";
            } 
            echo "</table>";
        }
        else
            echo " Nessuna persona &egrave; presente.";
       
		echo "<br> Numero abitanti risultanti: $all_rows<br>";

		// visualizza pagine
        $vis_pag = $config_path .'/../vis_pag.php';
        require $vis_pag;


        $result->free();
        $conn->close();	
        ?>

    </body>
</html>