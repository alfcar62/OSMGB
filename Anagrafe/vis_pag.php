<?php
/*
*** vis_pag.php
*** visualizzazione delle pagine in elenco
*** Se le pagine totali sono più di 1...
*** stampo i link per andare avanti e indietro tra le diverse pagine!
*/
   if($pag < 1){"<li class='page-item disabled'>
         <span class='page-link'>previous</span> </li>
         ";}
        if ($all_pages > 1){
            if ($pag > 1){
                echo " <li class='page-item'> <a class='page-link' href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag -$pag+1) . "\">";
                echo "Prima Pagina</a></li>&nbsp;";
            }
            // faccio un ciclo di tutte le pagine
        
      if($pag!=1){
      echo "<li class='page-item'><a class='page-link' href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag-1).     "\">";
      echo ($pag - 1 )."</a></li>&nbsp";}
      echo "<li class='page-item active'>  <span class='page-link'>" . $pag . "<span class='sr-only'>(current)</span>
      </span></li>&nbsp";
            for($i=1;$i<5 && $i<$all_pages;$i++){
      if($all_pages!=$pag && $all_pages >( $i+$pag)){
      echo "<li class='page-item'><a class='page-link' href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($pag+$i)  ."\">";
       
      echo ($pag + $i )."</a></li>&nbsp";}}
            if ($all_pages > $pag)
            {
                echo "<li class='page-item'><a class='page-link' href=\"" . $_SERVER['PHP_SELF'] . "?pag=" . ($all_pages) . "\">";
                echo "Ultima pagina";
            } 
        }
?>