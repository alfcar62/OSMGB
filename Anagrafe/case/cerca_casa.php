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

if (isset($_SESSION['ord_c']))
  $ord = $_SESSION['ord_c'];
else 
  $ord  = "ASC";

if (isset($_SESSION['campo_c']))
  $campo = $_SESSION['campo_c'];
else 
  $campo = "nome";

if(isset($_REQUEST["term"])){
    // Prepare a select statement
//    $sql = "SELECT nome FROM casa WHERE nome LIKE ?";
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
   $query .= " AND c.nome LIKE ?";
   $query .= " ORDER BY $campo " . $ord ;
   
 //  echo $query;
   if($stmt = mysqli_prepare($conn, $query)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_term);
        
        // Set parameters
        $param_term = '%'. $_REQUEST["term"] . '%';
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0){
                // Fetch result rows as an associative array
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    echo "<p>" . $row["nome"] . "</p>";
                }
            } else{
                echo "<p>No matches found</p>";
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