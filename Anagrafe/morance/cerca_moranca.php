<?php
$config_path = __DIR__;

$util1 = $config_path .'/../util.php';
require_once $util1;
setup();

$util2 = $config_path .'/../db/db_conn.php';
require_once $util2;

if (isset($_SESSION['cod_zona']))
  $cod_zona = $_SESSION['cod_zona'];
else 
 $cod_zona = "tutte";

//echo "cod_zona=". $cod_zona;
if (isset($_SESSION['ord_m']))
  $ord = $_SESSION['ord_m'];
else 
  $ord  = "ASC";

if (isset($_SESSION['campo']))
  $campo = $_SESSION['campo'];
else 
  $campo = "nome";

if(isset($_REQUEST["term"])){
    // Prepare a select statement
 //   $sql = "SELECT nome FROM morance WHERE nome LIKE ? ORDER BY nome";
    $query = "SELECT ";
    $query .= " m.id, m.nome, z.nome zona,m.id_mor_zona,m.id_osm,";
    $query .= " m.data_inizio_val, m.data_fine_val";
    $query .= " FROM morance m, zone z ";
    $query .= " WHERE m.data_fine_val IS NULL";
    $query .= " AND m.cod_zona = z.cod";
    if (isset($cod_zona) && ($cod_zona !='tutte'))
          $query .= " AND m.cod_zona = '$cod_zona'";
    $query .= " AND m.nome LIKE ?";
    $query .= " ORDER BY $campo " . $ord ;

// echo $query;
    if($stmt = mysqli_prepare($conn, $query)){
        // Bind variables to the prepared statement as parameters
       mysqli_stmt_bind_param($stmt, "s", $param_term);
        
        // Set parameters
        $param_term = $_REQUEST["term"] . '%';
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0){
                // Fetch result rows as an associative array
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
			    $mystr = utf8_encode ($row['nome']) ;
				echo "<p>" . $mystr . "</p>";
                }
            } else{
                echo "<p>nessun risultato soddisfa la ricerca</p>";
            }
        } else{
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
    }
     
    // Close statement
   mysqli_stmt_close($stmt);
}
 
// close connection
//mysqli_close($conn);
?>