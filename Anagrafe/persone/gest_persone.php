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
                if(inputVal.length>1){
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
	
	<?php
	if (isset($_SESSION['cod_zona']))
	  $cod_zona = $_SESSION['cod_zona'];
	else
      $cod_zona = "tutte"; 

	if (isset($_SESSION['id_casa']))
	  $id_casa = $_SESSION['id_casa'];
	else
      $id_casa = "tutte"; 
	 

    if (isset($_SESSION['decessi'])) 
	   {
           $_SESSION['old_decessi'] =  $_SESSION['decessi'];
		   $decessi = $_SESSION['old_decessi'];
		}

    if (isset($_POST['decessi']))		// arriva dal form stesso
        {
            $decessi = $_POST['decessi'];
            $_SESSION['decessi'] = $decessi;
        }  
    else 
        {
            if( isset($_SESSION['decessi']))		
                $decessi =  $_SESSION['decessi'];
			else
                $decessi = 'tutti'; 
        }
	 
//	 echo "1.decessi=". $decessi;
//	 echo "1.SESSION[decessi]=". $_SESSION['decessi'];

     if (isset($_SESSION['ord_p']))		//ordinamento ASC/DESC
	   $ord = $_SESSION['ord_p'];
	 else
       $ord = "ASC";
	 
	 if (isset($_SESSION['campo_p']))		// campo sul cui fare ordinamento
	   $campo = $_SESSION['campo_p'];
	 else
       $campo = "nominativo";

	 if(isset($_GET['pag']))			// pagina corrente
	   $pag= $_GET['pag'];
     else
	   $pag= 0;

	?>
	   <h2> Villaggio di NTchangue: Elenco persone</h2>

       <div class="search-box">
		    <form action='gest_persone.php' method='POST'><br>
            <input type="text" autocomplete="off" name='nome' placeholder="nominativo..." />
			<input type='submit' name= 'ricerca' class='button' value='Cerca'>
			<div class="result"></div>
            </form>
         <?php
		 $x_pag = 10;			// n. di record per pagina
         $ricerca = false;
         if(isset($_POST['ricerca']))		// se è stata richiesta la ricerca, recupera la pagina da visualizzare
		   {
            $pag = get_first_pag($conn, $_POST['nome'],$id_casa, $decessi, $cod_zona, $ord, $campo); 

			$ricerca = true;
 //			echo "pag=". $pag;
 //         echo "first=". $first;
		   }
         ?>
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

 

        $x_pag = 10;
        // Recupero il numero di pagina corrente.

        $pag=Paginazione($pag, "pag_p");	// Recupero il  numero di pagina corrente

	//	echo "pagina=". $pag;

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

		/* se è cambiato qualcosa riparto dalla prima pagina

		if (isset($_SESSION['decessi']) &&
		    isset($_SESSION['old_decessi']))
			{
		     if ($_SESSION['decessi'] != $_SESSION['old_decessi'])
				 $first = 0;
			}
        */
        $first = ($pag - 1) * $x_pag;

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

        /*
		*** caso di richiesto nuovo  ordinamento su campi id o nome
		*/
       if (isset($_POST['ord_id']) ||
		    isset($_POST['ord_nominativo']))
         {
          if (isset($_POST['ord_id']))		// cambiato ordinamento su id
		     $campo = 'id';
		  else 
			 $campo = 'nominativo';				// cambiato ordinamento su nome
             
          if ($ord == 'ASC')
				$ord = "DESC";
			else
				$ord = "ASC";
		  $first = 0;			// riparto dall'inizio
          $pag = 1;
        }
       else	
        {
            if (isset($_SESSION['campo_p']))
				$campo = $_SESSION['campo_p'];
		    else 
				$campo = "nominativo";

			 if (isset($_SESSION['ord_p']))
				$ord = $_SESSION['ord_p'];
		    else 
				$ord = "ASC";  	
         }
       $_SESSION['campo_p'] = $campo;
	   $_SESSION['ord_p'] = $ord;

        $result->free();

        $query = "SELECT ";
        $query .= " p.id, p.nominativo, p.sesso, p.data_nascita, p.data_morte,";
        $query .= " c.id as id_casa, c.id_moranca,c.nome nome_casa, m.nome nome_moranca,";
        $query .= " m.cod_zona, z.nome zona , c.id_casa_moranca, c.id_osm, ";
        $query .= " pc.cod_ruolo_pers_fam, rpf.descrizione,";
        $query .= " p.data_inizio_val, p.data_fine_val ";
        $query .= " FROM persone p";
        $query .= " INNER JOIN pers_casa pc ON  pc.id_pers = p.id";
        $query .= " INNER JOIN casa c ON  pc.id_casa = c.id";
        $query .= " INNER JOIN morance m ON  c.id_moranca = m.id";
        $query .= " INNER JOIN zone z ON  m.cod_zona = z.cod";
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
    
	    $vis_decessi = true;       
		if (isset($decessi))
		 {
		   if (($decessi == 'si') || ($decessi == 'tutti'))
		       $vis_decessi = true;
		   else 
		        $vis_decessi = false;
		 }

        if ($nr != 0)
        {
            echo "<table border>";
            echo "<tr>";  

            //nominativo (con possibilità di ordinamento)

            echo " <form method='post' action='gest_persone.php'>";
            echo "<th> nominativo <button class='btn center-block'  name='ord_nominativo'  value='nominativo' type='submit'><i class='fa fa-sort' title ='inverti ordinamento'></i> </button> </th></form>";
         
            //id (con possibilità di ordinamento)
            echo " <form method='post' action='gest_persone.php'>";
            echo "<th> id <button class='btn center-block'  name='ord_id'  value='id' type='submit'><i class='fa fa-sort' title ='inverti ordinamento'></i>  </button> </th></form>";

            echo "<th>sesso</th>";//Sesso		
            echo "<th>data nascita</th>";//Data Nascita
			if ($vis_decessi)
                 echo "<th>data decesso</th>";//Data Morte
            echo "<th>età</th>";//Età
            echo "<th>ruolo</th>";//Ruolo in famiglia
            echo "<th>casa</th>";//Casa
            echo "<th>moran&ccedil;a</th>";//Morança
			echo "<th>zona </th>";
            echo "<th>sulla mappa</th>";
            echo "<th>data inizio val";//Data val
            echo "<th>".$jsonObj->{$lang."Morance"}[9]."</th>";//Modifica
            echo "<th>".$jsonObj->{$lang."Morance"}[10]."</th>";//Elimina   
            echo "<th>".$jsonObj->{$lang."Persone"}[7]."</th>";//Casa  
            echo "<th>".$jsonObj->{$lang."Morance"}[12]."</th>";//Storico
            echo "</tr>";

            echo "<tr>";
            while ($row = $result->fetch_array())
            { 
                $mystr = utf8_encode ($row['nominativo']) ;
                echo "<td>$mystr</td>";
				echo "<td>$row[id]</td>";
                echo "<td>$row[sesso]</td>";
                echo "<td>$row[data_nascita]</td>";
				if ($vis_decessi)
                      echo "<td>".$row['data_morte']."</td>";

				// calcolo età (se vivente  o se dececuto)
				if (($row['data_morte'] != "") || ($row['data_morte'] != "0000-00-00"))
				      echo "<td>".date_diff(date_create($row['data_nascita']),
					              date_create($row['data_morte']))->y."</td>";
                 else
                       echo "<td>".date_diff(date_create($row['data_nascita']), 
					               date_create('today'))->y."</td>";
				
				

                echo "<td>$row[descrizione] ($row[cod_ruolo_pers_fam])</td>";
                echo "<td>$row[nome_casa]</td>";
                $mystr = utf8_encode ($row['nome_moranca']) ;
                echo "<td>$mystr</td>";
                echo "<td>$row[zona]</td>";
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



/*
*** funzione che, a seguito di una nuova ricerca, imposta la prima pagina da visualizzare
*** return: $pag (pagina da visualizzare)
***       
*/
function get_first_pag($conn, $nominativo, $id_casa, $decessi, $cod_zona, $ord, $campo)
{ 
	//echo "2.decessi=". $decessi;
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
	  if ($ord == "ASC")
	     $query .= " AND p.nominativo < '".$nominativo."'";
      else
	     $query .= " AND p.nominativo > '".$nominativo."'";
      $query .= " ORDER BY $campo " . $ord ;

// echo $query;

    $result = $conn->query($query);
    $cont=$result->num_rows;
// echo "cont=". $cont;  
    $result->free();

    $x_pag = 10;
    $pag= intval(abs($cont/$x_pag))+1;

    return $pag;
}
?>

</body>
</html>