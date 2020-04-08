<?php
$valore=$_POST["valore"] ;
$zona=$_POST["zona_richiesta"] ;
if($valore=="maschi")
{
   
header("Location:sessoMeF.php?zona_richiesta=$zona")
;

}
elseif($valore=="maggiorenni")
{
    header("Location:maggiorenni.php?zona_richiesta=$zona");

}elseif($valore=="fertili")
{
    header("Location:fertili.php?zona_richiesta=$zona");

}
elseif($valore=="fasce")
{
    header("Location:fasce.php?zona_richiesta=$zona");

}



?>