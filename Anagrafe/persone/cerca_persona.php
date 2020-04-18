<?php
$config_path = __DIR__;

$util1 = $config_path .'/../util.php';
require_once $util1;
setup();
isLogged("utente");

$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;

if (isset($_SESSION['id_casa']))
  $id_casa = $_SESSION['id_casa'];

if (isset($_SESSION['ord_p']))
  $ord = $_SESSION['ord_p'];
else 
  $ord  = "ASC";

if (isset($_SESSION['campo_p']))
  $campo = $_SESSION['campo_p'];
else 
  $campo = "nominativo";


 if( isset($_SESSION['decessi']))
  $decessi = $_SESSION['decessi'];
else
  $decessi = "tutti";

if (isset($_SESSION['cod_zona']))
  $cod_zona = $_SESSION['cod_zona'];

if(isset($_REQUEST["term"]))
{
    // Prepare a select statement
//    $sql = "SELECT nominativo FROM persone WHERE nominativo LIKE ?";
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
	  $query .= " AND p.nominativo LIKE ?";
      $query .= " ORDER BY $campo " . $ord ;
     
 //   echo $query;

    if($stmt = mysqli_prepare($conn, $query))
	 {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_term);
        
        // Set parameters
        $param_term = '%' . $_REQUEST["term"] . '%';
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt))
		 {
            $result = mysqli_stmt_get_result($stmt);
            
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0)
			 {
                // Fetch result rows as an associative array
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
				 {
				   $mystr = utf8_encode ($row['nominativo']) ;
                   echo "<p>" . $mystr . "</p>";
                 }
             } 
			else
                echo "<p>No matches found</p>";
        } 
		else
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
     }
     
    // Close statement
   mysqli_stmt_close($stmt);
}
 
// close connection
//mysqli_close($conn);
?>