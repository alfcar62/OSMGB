<?php
/* Autore:Ferraiuolo
*** Descrizione:Gestione delle case
*** 13/03/2020  Carlone: modificata la query (per visualizzare anche se non c'è il capo famiglia)
*** 11/03/2020 Ferraiuolo  Modifica:aggiunta visualizzazione della casa con relativo zoom in caso si passi 
*** con il cursore sopra
***29/03/2020: Ferraiuolo: aggiunta del div modal,script js per creare lo zoom quando si clicca sulla foto della casa
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;

setup();
unsetPag(basename(__FILE__)); 
isLogged("gestore");
?>
<html>
    <link rel="stylesheet" type="text/css" href="../css/style1.css">
    <link rel="stylesheet" type="text/css" href="gest_case_temp_css.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <!--<script src="gest_case_js.js"></script>-->
    <script type="text/javascript">
        $(document).ready(function(){
            $('.search-box input[type="text"]').on("keyup input", function(){
                /* Get input value on change */
                var inputVal = $(this).val();
                var resultDropdown = $(this).siblings(".result");
                if(inputVal.length>2){
                    $.get("cerca_casa.php", {term: inputVal}).done(function(data){
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

	if (isset($_POST['cod_zona']))
     {
       $cod_zona = $_POST['cod_zona']; 
       $_SESSION['cod_zona'] = $cod_zona;
     }  
    else 
     {
       if( isset($_SESSION['cod_zona']) &&  ($_SESSION['cod_zona'] != 'tutte'))		
                $cod_zona =  $_SESSION['cod_zona'];
	   else  $cod_zona = "tutte";
     } 

     if (isset($_SESSION['ord_c']))		//ordinamento ASC/DESC
	   $ord = $_SESSION['ord_c'];
	 else
       $ord = "ASC";
	 
	 if (isset($_SESSION['campo_c']))		// campo sul cui fare ordinamento
	   $campo = $_SESSION['campo_c'];
	 else
       $campo = "nome";

	 if(isset($_GET['pag']))			// pagina corrente
	   $pag= $_GET['pag'];
     else
	   $pag= 0;
	?>
	  <h2> Villaggio di N'Tchangue: elenco case</h2>
    <?php
      echo "<div style='float:left'>";
	  echo "<a href='vis_sto_tot_case.php'> Storia delle case <IMG SRC='../img/history.png'></a>";
	  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	  echo"<a href='export_casa.php'>Export su excel<i class='fa fa-file-excel-o fa-2x'></i></a>&nbsp;";		
      echo "</div>";
      echo "<div style='clear:both;'></div>";
    ?>
     <div class="search-box">
		    <form action='gest_case.php' method='POST'><br>
            <input type="text" autocomplete="off" name='nome' placeholder="nome casa..." />
			<input type='submit' name= 'ricerca' class='button' value='Cerca'>
            <div class="result"></div>
			</form>
         <?php
		 $x_pag = 10;			// n. di record per pagina
		 $ricerca= false;
         if(isset($_POST['ricerca']))		// se è stata richiesta la ricerca, recupera la pagina da visualizzare
		   {
            $pag = get_first_pag($conn, $_POST['nome'], $cod_zona,  $ord, $campo); 
			$ricerca = true;
//			echo "ricerca: pag=". $pag;
		   }
         ?>
  <!--      </div>-->
		  <div id="lb-back">
            <div id="lb-img"></div>
        </div>
        <!-- Modal:div che compare quando si clicca sull'immagine -->
        <div id="myModal" class="modal">

            <!-- The Close Button -->
            <span class="close">&times;</span>

            <!-- Modal Content (The Image) -->
            <img class="modal-content" id="img01">
        </div>
        <?php 

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
            $cod_zona = "tutte";
        } 

        // Creo una variabile dove imposto il numero di record 
        // da mostrare in ogni pagina
        $x_pag = 10;  
        
		if (!$ricerca)
          $pag=Paginazione($pag, "pag_c");	// Recupero il  numero di pagina corrent

	 //  echo "paginazionea: pag=". $pag;

      
        // Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella 

        $query = "SELECT count(c.id) as cont";
        $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
        $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
        $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
        $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
        $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
        $query .= " WHERE c.DATA_FINE_VAL is null";
		if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '{$cod_zona}'";
        $result = $conn->query($query);
        $row = $result->fetch_array();
        $all_rows= $row['cont'];

        //  definisco il numero totale di pagine
        $all_pages = ceil($all_rows / $x_pag);

       // Calcolo da quale record iniziare
		if (!$ricerca)
          $first = ($pag-1) * $x_pag ;
		else 
		  $first = ($pag) * $x_pag ;
//        echo "ricerca=".$ricerca;
//		 echo "pag=".$pag;
//        echo "first=".$first;


        //Select option per la scelta della zona
        echo "<form action='gest_case.php' method='POST'><br>";
        echo   "Zona: <select name='cod_zona'>";
        $result = $conn->query("SELECT * FROM zone");
        $nz=$result->num_rows;
        echo "<option value='tutte'>  tutte </option>";
        for($i=0;$i<$nz;$i++)
        {
            $row = $result->fetch_array();

            if(isset($cod_zona) && $cod_zona == $row["COD"])
                echo "<option value='".$row["COD"]."' selected>". $row["NOME"]." </option>";
            else
                echo "<option value='".$row["COD"]."'>".$row["NOME"]."</option>";
        }
        echo "</select>";
        echo " <input type='submit' class='button' value='Conferma'>";
        echo " </form>";
        echo " </div>";
		
		echo"<a href='ins_casa.php'>Inserimento nuova casa <i class='fa fa-plus-square fa-2x' ></i></a>&nbsp;";

		/*
		*** caso di richiesto nuovo  ordinamento su campi id o nome
		*/
	   if (isset($_SESSION['ord_c']))
				$ord = $_SESSION['ord_c'];
	   else 
				$ord = "ASC"; 
	   
	   if (isset($_SESSION['campo_c']))
				$campo = $_SESSION['campo_c'];
		else 
				$campo = "nome";
      
	   if (isset($_POST['ord_id']) ||
		    isset($_POST['ord_nome']))
         {
          if (isset($_POST['ord_id']))		// cambiato ordinamento su id
		     $campo = 'id';
		  else 
			 $campo = 'nome';				// cambiato ordinamento su nome
             
          if ($ord == "ASC")
				$ord = "DESC";
			else
				$ord = "ASC";
		  $first = 0;			// riparto dall'inizio
          $pag = 1;
        }
      
       $_SESSION['campo_c'] = $campo;
	   $_SESSION['ord_c'] = $ord;

/*
*** 13/3/2020: A. Carlone. Modificata la query, per visualizzare anche case senza capo famiglia
*/
        $query = "SELECT c.id, c.nome,";
        $query .= " z.nome zona, c.id_moranca, m.nome nome_moranca,";
        $query .= " c.nome, p.id id_pers, p.nominativo, c.id_osm as id_osm, ";
        $query .= " c.data_inizio_val data_val, c.data_fine_val";
        $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
        $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
        $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
        $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
        $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
        $query .= " WHERE c.DATA_FINE_VAL is null";
        if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '{$cod_zona}'";
        $query .= " ORDER BY $campo " . $ord ;
        $query .= " LIMIT $first, $x_pag";
        $result = $conn->query($query);  
  //      echo $query;

        if ($result->num_rows !=0)
        {
            echo "<table border>";
            echo "<tr>";
            echo "<th>foto</th>";

			if ($ord == "ASC")
				$myclass = "fa fa-arrow-circle-down";
			else
				$myclass = "fa fa-arrow-circle-up";
			//nome casa  (con possibilità di ordinamento)

			echo " <form method='post' action='gest_case.php'>";
            echo "<th> nominativo <button class='btn center-block'  name='ord_nome'  value='nome' type='submit'><i class='".$myclass ."' title ='inverti ordinamento'></i> </button> </th></form>";
 

            //id (con possibilità di ordinamento)
            echo " <form method='post' action='gest_case.php'>";
            echo "<th> id <button class='btn center-block'  name='ord_id'  value='id' type='submit'><i class='".$myclass ."' title ='inverti ordinamento'></i>  </button> </th></form>";
            
     
            echo "<th>zona</th>";
			echo "<th>moran&ccedil;a</th>";
            echo "<th>id moranca</th>";
            echo "<th>capo famiglia</th>";
            echo "<th>id capo famiglia</th>";
            echo "<th>n.abitanti</th>";
            echo "<th>sulla mappa</th>";
            echo "<th>data inizio val</th>";
            echo "<th>Modifica</th>";
            echo "<th>Elimina</th>";
            echo "<th>Persone</th>";
            echo "<th>Storico </th>";
            echo "</tr>";

            while ($row = $result->fetch_array())
            {
                echo "<tr>";
                $immagine=glob('immagini/'.$row['id'].'.*');
                if($immagine != null)
                    echo "<td><div ><img src='$immagine[0]' class='modal_image' style='display: block; margin-left: auto; margin-right: auto;width:35px;height:30px'  ></div></td> ";
                else{
                    echo '<td><i class="fa fa-image"></i></td>';
                }
                echo "<td>$row[nome]</td>";
				echo "<td>$row[id]</td>";
                echo "<td>$row[zona]</td>";

			    $mystr = utf8_encode ($row['nome_moranca']) ;
                echo "<td>$mystr</th>";

                echo "<td>$row[id_moranca]</td>";

                $mystr = utf8_encode ($row['nominativo']) ;
                echo "<td>$mystr</td>";
                echo "<td>$row[id_pers]</td>";

                $query2="SELECT COUNT(pers_casa.ID_PERS) as persone from pers_casa WHERE ID_CASA='$row[id]'";
                $result2 = $conn->query($query2);
                $row2 = $result2->fetch_array();
                echo "<td>$row2[persone]</th>";

                $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
                if ($row['id_osm'] != null && $row['id_osm'] != "0")
                { 
                    echo "<td>$row[id_osm]<a href=$osm_link target=new><img src='../img/marker.png' ></a></td>"; 
                }
                else
                { 
                    echo "<td>&nbsp;</td>";
                }
                echo "<td>$row[data_val]</td>";

                echo " <form method='post' action='mod_casa.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><img src='../img/wrench.png' > </button> ". "</th></form>";

                echo " <form method='post' action='del_casa.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><img src='../img/trash.png' ></button> ". "</th></form>";

                echo " <form method='post' action='mostra_persone.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><img src='../img/people.png' ></button> ". "</th></form>";

                echo " <form method='post' action='vis_casa_sto.php'>";
                echo "<th><button class='btn center-block' name='id_casa'  value='$row[id]' type='submit';'><img src='../img/history.png' ></button> ". "</th></form>";
                echo "</tr></form>";
            }
            echo "</table>";
        }

       echo "<br> Numero case risultanti: $all_rows<br>";
       $vis_pag = $config_path .'/../vis_pag.php';
       require $vis_pag;

       $result->free();
       $conn->close();	
      ?>  

    </body>
    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Prende l'immagine e le inserisce nel div modal (codice di W3Schools modificato con l'aggiunta delle classi)
        var img = document.getElementsByClassName('modal_image');
        for(var i=0; i<img.length; i++){
            var modalImg = document.getElementById("img01");
            var captionText = document.getElementById("caption");
            img[i].addEventListener('click',function(){
                modal.style.display = "block";
                modalImg.src = this.src;
                captionText.innerHTML = this.alt;
            })
        }

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() { 
            modal.style.display = "none";
        }
    </script>

<?php
/*
*** funzione che, a seguito di una nuova ricerca, imposta la prima pagina da visualizzare
*** return: $pag (pagina da visualizzare)
***       
*/
function get_first_pag($conn, $nome, $cod_zona, $ord, $campo_ord)
{ 
   $nome = utf8_decode($nome);
// recupero l'id casa
   $query = "SELECT id FROM casa  WHERE nome = '{$nome}'";
   $result = $conn->query($query);
   $row = $result->fetch_array();
   $id = $row['id'];
   $result->free();


   $query = "SELECT c.id, c.nome,";
   $query .= " z.nome zona, c.id_moranca, m.nome nome_moranca,";
   $query .= " p.id id_pers, p.nominativo, c.id_osm as id_osm, ";
   $query .= " c.data_inizio_val data_val, c.data_fine_val";
   $query .= " FROM morance m INNER JOIN casa c ON m.id = c.id_moranca ";
   $query .= " INNER JOIN zone z  ON  z.cod = m.cod_zona ";
   $query .= " LEFT JOIN pers_casa pc ON c.id  = pc.id_casa ";
   $query .="  AND pc.cod_ruolo_pers_fam = 'CF'";
   $query .="  LEFT JOIN persone p ON p.id = pc.id_pers";
   $query .= " WHERE c.DATA_FINE_VAL is null";
   if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '{$cod_zona}'";  

   if ($campo_ord == "nome")
	   $campo_ord = "c.nome";
   else
       $campo_ord = "c.id";

   if ($campo_ord == "c.nome")
    {
      if ($ord == "ASC")
	     $query .= " AND $campo_ord  <= '".$nome."'";
      else
	      $query .= " AND $campo_ord >= '".$nome."'";
    }
   else
    {
      if ($ord == "ASC")
	     $query .= " AND $campo_ord  <= ".$id;
      else
	      $query .= " AND  $campo_ord>= ".$id;
    }
//	  $query .= " AND c.nome >= '".$nome."'";
   $query .= " ORDER BY $campo_ord " . $ord ;

//  echo "get_first_pag:". $query;

  $result = $conn->query($query);
  $cont=$result->num_rows;
 // echo "cont=". $cont;  
  $result->free();

  $x_pag = 10;
  $resto = $cont%$x_pag;
// echo "resto=", $resto;
// echo "x_pag=", $x_pag;
// echo "intval(abs($cont/$x_pag))=".intval(abs($cont/$x_pag));
 if ($resto ==0)
       $pag= intval(abs($cont/$x_pag))-1;
	 else
       $pag= intval(abs($cont/$x_pag));
// echo "esco da first_pag, pag=", $pag;
 return $pag;
}
?>
</body>
</html>