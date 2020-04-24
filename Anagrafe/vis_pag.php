<?php
/*
*** vis_pag.php
*** visualizzazione delle pagine in elenco
*** Se le pagine totali sono più di 1...
*** stampo i link per andare avanti e indietro tra le diverse pagine!
*/
 if ($all_pages > 1)
 {
  echo "Pagina:".$pag. "/". $all_pages."&nbsp;&nbsp;";
  if ($pag > 1)
   {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag - 1) . "\">";
      echo "<<- Pagina Indietro</a>&nbsp;&nbsp;&nbsp;";
   }
  if ($all_pages > $pag)
   {
         echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag + 1) . "\">";
         echo "Pagina Avanti ->><br><br></a>";
   } 
   // faccio un ciclo di tutte le pagine
  $cont=0;
  for ($p=1; $p<=$all_pages; $p++) 
   {
    if ($cont>=50)
     {
       echo "<br>";
       $cont=0;
     }
    $cont++;
       // per la pagina corrente non mostro nessun link ma la evidenzio in bold
       // all'interno della sequenza delle pagine
    if ($p == $pag)
		echo "<b>" . $p . "</b>&nbsp;";
       // per tutte le altre pagine stampo il link
    else
      { 
         echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . $p . "\">";
         echo $p . "</a>&nbsp;";
      } 
    }

  }
?>