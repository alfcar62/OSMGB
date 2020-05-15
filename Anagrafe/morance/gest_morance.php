<?php
/*
*** Gest_morance.php: Gestione delle Morance
*** 
*** 11/3/2020: A.Carlone: migliorata gestione zone e ordinamento su id e nome moranca
*** 27/02/20 : Gobbi: Implementazione della gestione multilingue
*** 2/2/2020: A. Carlone: prima implementazione
*/
$config_path = __DIR__;
$util = $config_path .'/../util.php';
require $util;

$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;
 
setup();
isLogged("gestore");
unsetPag(basename(__FILE__));

$lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA";
$jsonFile=file_get_contents("../gestione_lingue/translations.json");//Converto il file json in una stringa
$jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto

if(isset($_SESSION['errore']) && $_SESSION['errore']=='error'){echo "<script>alert('Esistono case nella moranca: impossibile cancellare')</script>";
                                                              }
$_SESSION['errore']=null;

?>
<html>
    <link rel="stylesheet" type="text/css" href="../css/style1.css">
    <link rel="stylesheet" type="text/css" href="gest_morance_temp_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.search-box input[type="text"]').on("keyup input", function(){
                /* Get input value on change */
                var inputVal = $(this).val();
                var resultDropdown = $(this).siblings(".result");
                if(inputVal.length>1){
                    $.get("cerca_moranca.php", {term: inputVal}).done(function(data){
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

     if (isset($_SESSION['ord_m']))		//ordinamento ASC/DESC
	   $ord = $_SESSION['ord_m'];
	 else
       $ord = "ASC";
	 
	 if (isset($_SESSION['campo_m']))		// campo sul cui fare ordinamento
	   $campo = $_SESSION['campo_m'];
	 else
       $campo = "nome";

	 if(isset($_GET['pag']))			// pagina corrente
	   $pag= $_GET['pag'];
     else
	   $pag= 1;
	?>
	    <h2><center><IMG SRC="/OSM/Anagrafe/img/moranca3.png"  WIDTH="50" HEIGHT="30"> Elenco moran&ccedil;as <IMG SRC="/OSM/Anagrafe/img/moranca3.png" WIDTH="50" HEIGHT="30" ></center></h2>
   <?php
    echo "<div style='float:left'>";
		echo "<a href='vis_sto_tot_morance.php'> Storia delle moran&ccedil;as <IMG SRC='../img/history.png'></a>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo"<a href='export_moranca.php'>Export su excel <IMG SRC='../img/excel_2.png'></a>&nbsp;";		
    echo "</div>";
    echo "<div style='clear:both;'></div>";
	?>
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
 
    <div class="search-box">
	<form action='gest_morance.php' method='POST'><br>
    <input type="text" autocomplete="off" name='nome' placeholder="nome..." />
	<input type='submit' name= 'ricerca' class='button' value='Cerca'>
	<div class="result"></div>
    </form>
<?php
	$x_pag = 10;			// n. di record per pagina
	$ricerca = false;
    if(isset($_POST['ricerca']))		// se è stata richiesta la ricerca, recupera la pagina da visualizzare
	 {
      $pag = get_first_pag($conn, $_POST['nome'], $cod_zona, $ord, $campo); 	
	  $ricerca= true;
// 	  echo "dopo get_first_pag pag=". $pag;
	 }
?>
<!--        </div>-->
        <?php 
        if (!$ricerca)
		{
          $pag=Paginazione($pag, "pag_m");	// Recupero il  numero di pagina corrente
//		  echo "dopo Paginazione pagina=". $pag;
		}
        
//		echo "pagina=". $pag;

       
        // Uso mysql_num_rows per contare il totale delle righe presenti all'interno della tabella 
        $query = "SELECT count(id) as cont FROM morance where DATA_FINE_VAL IS null";
        if (isset($cod_zona) && $cod_zona != 'tutte')
            $query .= " AND cod_zona ='". $cod_zona ."'";

//         echo $query;
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
  //      echo "ricerca=".$ricerca;
	//	 echo "pag=".$pag;
   //     echo "first=".$first;

        //Select option per la scelta della zona
        echo "<form action='gest_morance.php' method='POST'><br>";
        echo  "Zona: <select name='cod_zona'>";
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
        echo " <input type='submit' class='button' value='". $jsonObj->{$lang."Morance"}[4]."'>";//Conferma
        echo " </form>";
		echo " </div>";
		
		echo"<a href='ins_moranca.php'>Inserimento nuova moran&ccedil;a <i class='fa fa-plus-circle fa-2x'></i></a>&nbsp;";


		/*
		*** caso di richiesto nuovo  ordinamento su campi id o nome
		*/
	   if (isset($_SESSION['campo_m']))
				$campo = $_SESSION['campo_m'];
		    else 
				$campo = "nome";

			 if (isset($_SESSION['ord_m']))
				$ord = $_SESSION['ord_m'];
		    else 
				$ord = "ASC";  
				
       if (isset($_POST['ord_id']) ||
		    isset($_POST['ord_nome']))
         {
//		  echo " cambiato campo o ord";
          if (isset($_POST['ord_id']))		// cambiato ordinamento su id
		     $campo = 'id';
		  else 
			 $campo = 'nome';				// cambiato ordinamento su nome
             
          if ($ord == 'ASC')
				$ord = "DESC";
			else
				$ord = "ASC";
		  $first = 0;			// riparto dall'inizio
          $pag = 1;
        }
   
       $_SESSION['campo_m'] = $campo;
	   $_SESSION['ord_m'] = $ord;

       $query = "SELECT ";
       $query .= " m.id, m.nome, z.nome zona,m.id_mor_zona,m.id_osm,";
       $query .= " m.data_inizio_val, m.data_fine_val";
       $query .= " FROM morance m, zone z ";
       $query .= " WHERE m.data_fine_val IS NULL";
       $query .= " AND m.cod_zona = z.cod";
       if (isset($cod_zona) && ($cod_zona !='tutte'))
            $query .= " AND m.cod_zona = '$cod_zona'";
       $query .= " ORDER BY $campo " . $ord ;
       $query .= " LIMIT $first, $x_pag";

 //    echo $query;
       $result = $conn->query($query);
       $numero=$result->num_rows;
       if ($result->num_rows !=0)
        {
            echo "<table border>";
            echo "<tr>";

			//foto
            echo "<th>foto</th>";

			if ($ord == "ASC")
				$myclass = "fa fa-arrow-circle-down";
			else
				$myclass = "fa fa-arrow-circle-up";

			//nome Moranca  (con possibilità di ordinamento)

			echo " <form method='post' action='gest_morance.php'>";
            echo "<th> nome <button class='btn center-block'  name='ord_nome'  value='nome' type='submit'><i class='".$myclass ."' title ='inverti ordinamento'></i> </button> </th></form>";
 

            //id (con possibilità di ordinamento)
            echo " <form method='post' action='gest_morance.php'>";
            echo "<th> id <button class='btn center-block'  name='ord_id'  value='id' type='submit'><i class='".$myclass ."' title ='inverti ordinamento'></i>  </button> </th></form>";
           
            echo "<th>zona</th>";//Zona
            echo "<th>progr. zona</th>";//progr nella zona
			echo "<th>numero case</th>"; 
			echo "<th>numero abitanti</th>"; 

            echo "<th> sulla mappa";
            echo "<th>data inizio val";//data_val
            echo "<th>".$jsonObj->{$lang."Morance"}[9]."</th>";//Modifica
            echo "<th>".$jsonObj->{$lang."Morance"}[10]."</th>";//Elimina
            echo "<th>".$jsonObj->{$lang."Morance"}[11]."</th>";//Case
            echo "<th>Storico";//Storico

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

                $mystr = utf8_encode ($row['nome']) ;
                echo "<td>$mystr</td>";

				echo "<td>$row[id]</td>";
                echo "<td>$row[zona]</td>";
                echo "<td>$row[id_mor_zona]</td>";

                $id_moranca = $row['id'];

                $n_case = get_num_case($conn, $id_moranca);
                echo "<td>$n_case</th>";

                $n_abitanti = get_num_abitanti($conn, $id_moranca);
                echo "<td>$n_abitanti</th>";

              
                // va sulla mappa OSM con id_OSM
                $osm_link = "https://www.openstreetmap.org/way/$row[id_osm]";
                if ($row['id_osm'] != null && $row['id_osm'] != "0")
                { 
                    echo "<td>$row[id_osm]". " <a href=$osm_link target=new> <img src='../img/marker.png' ></a></td>"; 	   
                }
                else
                { 
                    echo "<td>&nbsp;</td>";
                }  

                echo "<td>$row[data_inizio_val]</td>";

                echo " <form method='post' action='mod_moranca.php'>";
                echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><img src='../img/wrench.png'> </button> ". "</th></form>";

                echo " <form method='post' action='del_moranca.php'>";
                echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><img src='../img/trash.png'> </button> ". "</th></form>";

                echo " <form method='post' action='mostra_case.php'>";
                echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><img src='../img/house.png'></button> ". "</th></form>"; 

                echo " <form method='post' action='vis_moranca_sto.php'>";
                echo "<th><button class='btn center-block' name='id_moranca'  value='$row[id]' type='submit';'><img src='../img/history.png'> </button> ". "</th></form>";    
                echo "</tr>";
				
            }      
            echo "</table>";      
        }
 
        echo "<br> Numero moran&ccedil;e risultanti: $all_rows<br>";

		// visualizza pagine
		echo "<div class='pagi'><nav aria-label='...' > <ul class='pagination'>";
        $vis_pag = $config_path .'/../vis_pag.php';
        require $vis_pag;
		echo "</div></ul></nav>";

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
</html>

<?php

/*
*** funzione che, a seguito di una nuova ricerca, imposta la prima pagina da visualizzare
*** return: $pag (pagina da visualizzare)
***       
*/
function get_first_pag($conn, $nome, $cod_zona, $ord, $campo_ord)
{ 
 $nome = utf8_decode($nome);
	// recupero l'id moranca
 $query = "SELECT id id_m FROM morance  WHERE nome = '{$nome}'";
 $result = $conn->query($query);
 $row = $result->fetch_array();
 $id = $row['id_m'];
 $result->free();

 // Prepare a select statement
 $query = "SELECT ";
 $query .= " m.id, m.nome, z.nome zona,m.id_mor_zona,m.id_osm,";
 $query .= " m.data_inizio_val, m.data_fine_val";
 $query .= " FROM morance m, zone z ";
 $query .= " WHERE m.data_fine_val IS NULL";
 $query .= " AND m.cod_zona = z.cod";
 if (isset($cod_zona) && ($cod_zona !='tutte'))
      $query .= " AND m.cod_zona = '". $cod_zona."'";

 if ($campo_ord == "nome")
	   $campo_ord = "m.nome";
 else
       $campo_ord = "m.id";

 if ($campo_ord == "m.nome")
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

// echo $query;

 $result = $conn->query($query);
 $cont=$result->num_rows;
// echo "cont=". $cont;  
 $result->free();

 $x_pag = 10;
    
 $resto = $cont%$x_pag;
 //echo "resto=", $resto;
 //echo "x_pag=", $x_pag;
 //echo "intval(abs($cont/$x_pag))=".intval(abs($cont/$x_pag));
 
 if ($resto ==0)
       $pag= intval(abs($cont/$x_pag))-1;
	 else
       $pag= intval(abs($cont/$x_pag));

//echo "esco da first_pag, pag=", $pag;
 return $pag;
}

function get_num_case($conn, $id_moranca)
{ 
 $query2 = "SELECT COUNT(id) as n_case from casa WHERE id_moranca= $id_moranca";
 $result2 = $conn->query($query2);
 $row2 = $result2->fetch_array();
 return($row2['n_case']);
}

function get_num_abitanti($conn, $id_moranca)
{ 
  $query2 =	"SELECT count(persone.id) as n_persone  from persone ";
  $query2 .= " inner join pers_casa on pers_casa.ID_PERS=persone.ID ";
  $query2 .= " inner join casa on pers_casa.ID_casa=casa.ID";
  $query2 .= " inner join morance on casa.ID_moranca=morance.ID";
  $query2 .= " AND morance.id = $id_moranca";
  $result2 = $conn->query($query2);
  $row2 = $result2->fetch_array();
  return($row2['n_persone']);
}

?>

</body>
</html>
