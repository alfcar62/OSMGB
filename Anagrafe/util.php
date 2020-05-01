<?php
/******************* Util.php *************************/
//Data ultima modifica:27/02/2020
//Descrizione:Implementazione della gestione multilingue attraverso un file .json Autore:Gobbi Dennis
//Descrizione:Gestione degli utenti Autore:Ferraiuolo Pasquale
//25/03/2020: Ferraiuolo: Aggiunta menu a tendina e nome utente nella videata
?>
<?php
/***************************** StampaNavbar *****************************/

function stampaNavbar()
{
    //echo getcwd();

    $lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA"; //Se nessuna lingua è stata scelta,verrà messa come default quella italiana
    $lang= strtoupper($lang);
    $jsonFile=file_get_contents(__DIR__ ."/gestione_lingue/translations.json");//Converto il file json in una stringa
    $jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
?>
<ul>
 <li class="titolo"><a href="/OSM/Anagrafe/index.php"><b>N'Tchangue<br> AnagrafeWEB<br></a></li>           
 <?php
 if (login())
   {
    if($_SESSION['tipo']!="utente")
	 {
 ?>   
    <li><a href="/OSM/Anagrafe/morance/gest_morance.php">Moran&ccedil;as<br><i class="fa fa-home"></i><i class="fa fa-home"></i><i class="fa fa-home"></i></a></li>
    <li><a href="/OSM/Anagrafe/case/gest_case.php"><?php echo ($jsonObj->{$lang."Navbar"}[4])?><br><i class="fa fa-home"></i></a></li><!--Case --> 
    <li><a href="/OSM/Anagrafe/persone/gest_persone.php"><?php echo ($jsonObj->{$lang."Navbar"}[5])?><br><i class='fa fa-male'></i><i class='fa fa-female'><i class='fa fa-male'><i class='fa fa-female'></i></i></i></a></li><!--Persone --> 
    <li><a href="/OSM/Anagrafe/OSM/db2geojson.php" target='mapcase'> Mappa case<br><i class="fa fa-home"></i><i class='fa fa-globe'></i> </a></li>-->
    <li><a href="https://www.openstreetmap.org/search?query=ntchangue#map=16/12.0039/-15.5081" target="osm">OSM<br><i class='fa fa-globe'></i></a></li>

    <?php
      } ?>
    <li><a href="/OSM/Anagrafe/stat/statistiche.php"><?php echo ($jsonObj->{$lang."Navbar"}[9])?><br><i class="fa fa-pie-chart"></i></a></li>
    <?php  if($_SESSION['tipo']=="admin")
	 {
	  echo "<li><a href='/OSM/Anagrafe/utenti/gestione_utenti.php'>".$jsonObj->{$lang."Navbar"}[10]."<br><i class='fa fa-user'></i></a></li>";// Utenti

     //echo "<li><a href='/OSM/Anagrafe/utility.php'>".$jsonObj->{$lang."Navbar"}[11]."</a></li>";
     // echo "<li><a href='https://drive.google.com/file/d/1VOXNtxo_ULb5xbqlJeVmjNz9vhz2insi/view?usp=sharing' target=new>Segnalazioni</a></li>";
    ?>
    <?php
      }  
	?>
    <li><div class="dropdown">
    <button class="dropbtn">
    <?php echo $_SESSION['nome']; ?>
    <i class="fa fa-caret-down"></i>
    </button>
    <div class="dropdown-content">
    <a href="/OSM/Anagrafe/utenti/area_personale.php">Area personale</a>
    <a href='/OSM/Anagrafe/logout.php'>Esci <IMG SRC='/OSM/Anagrafe/img/ico-logout.png' WIDTH='30' HEIGHT='28' BORDER='0' ALT='Esci'></IMG></a>
    </div>
    </div></li>
    <li>
    <a href="#" onclick="myFx()" class="globe">
    <img src="/OSM/Anagrafe/gestione_lingue/output-onlinepngtools.png" WIDTH='36' HEIGHT='33' BORDER='0' ALT="LANG" class="globe">
    </a>
    </li>
    <div id="dropMenu">
     <!--Il tag option del select non supporta le img,ho optato quindi per la rimozione di un form e al posto di esso ho messo dei link con href una pagina php con richiesta get -->
     <a href="/OSM/Anagrafe/gestione_lingue/gestione_lingue.php?lang=EN" >
     <img src="/OSM/Anagrafe/gestione_lingue/en_flag.png" WIDTH='30' HEIGHT='15' class="flag" alt="EN"></a><br>
     <a href="/OSM/Anagrafe/gestione_lingue/gestione_lingue.php?lang=ITA">
     <img src="/OSM/Anagrafe/gestione_lingue/ita_flag.png" WIDTH='30' HEIGHT='15' class="flag" alt="ITA"></a>
    </div>
    <?php
    }
    else
    {
    ?>
    <li><a href="/OSM/Anagrafe/info/chisiamo.php"><?php echo ($jsonObj->{$lang."Navbar"}[0])?></a></li><!--Chi siamo --> 
    <li><a href="/OSM/Anagrafe/info/progetto.php"><?php echo ($jsonObj->{$lang."Navbar"}[1])?></a></li><!--Il progetto --> 
    <li><a href="/OSM/Anagrafe/login.php"><IMG SRC="/OSM/Anagrafe/img/ico-login.png" ALT="Entra"></a></li> <!--Entra -->      
    <li>
        <a href="#" onclick="myFx()" class="globe">
            <img src="/OSM/Anagrafe/gestione_lingue/output-onlinepngtools.png" WIDTH='36' HEIGHT='33' BORDER='0' ALT="LANG" class="globe">
        </a>
    </li>
    <div id="dropMenu">
        <!--Il tag option del select non supporta le img,ho optato quindi per la rimozione di un form e al posto di esso ho messo dei link con href una pagina php con richiesta get -->
        <a href="/OSM/Anagrafe/gestione_lingue/gestione_lingue.php?lang=EN" >
            <img src="/OSM/Anagrafe/gestione_lingue/en_flag.png"  WIDTH='30' HEIGHT='15' class="flag" alt="EN">
        </a>
        <a href="/OSM/Anagrafe/gestione_lingue/gestione_lingue.php?lang=ITA">
            <img src="/OSM/Anagrafe/gestione_lingue/ita_flag.png"   WIDTH='30' HEIGHT='15' class="flag" alt="ITA">
        </a>
    </div>
</ul>
<?php
    }	 
?>
</ul>
<script>
    function myFx(){//Funzione per far comparire il dropdown menù 
        var show=document.getElementById("dropMenu").style.display;
        console.log(show);
        if(show=="none" || show=="")document.getElementById("dropMenu").style.display="inline";
        if(show=="inline" )document.getElementById("dropMenu").style.display="none";
    }
    window.onclick = function(){
        if(!event.target.matches(".globe")){
            document.getElementById("dropMenu").style.display="none";
            console.log("Clickato fuori dall'icona globo");
        }
    }
</script>

<script>

function tooltip(event)	// gestione tooltip
{
  document.getElementById("error").style.visibility="visible";
  if(event.type=="mouseover")
   {
    document.getElementById("error").style.visibility="visible";
   }
  else if(event.type=="mouseout")
	  {
        document.getElementById("error").style.visibility="hidden";
      }
 }

function tooltip2(event)	// gestione tooltip
	{
      document.getElementById("error2").style.visibility="visible";
      if(event.type=="mouseover")
		{
           document.getElementById("error2").style.visibility="visible";
        }
       else if(event.type=="mouseout")
		{
          document.getElementById("error2").style.visibility="hidden";
        }
     }
 </script>

<script> 
function PwChecker()		// controllo password
 {
  var pw=document.getElementById("psw").value;
  console.log(pw);
  var pattern=new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})","g");
  var isStrong=pattern.test(pw);
  if(isStrong){
  console.log("strong");
  $("#form").submit();
  }
  else 
   alert("Password non valida!\nInserire una password di 8 caratteri con un carattere maiuscolo,minuscolo,un numero e un carattere speciale tra questi:'!' '@' '#' '\$' '%' '\^' '&' '\*' '\_'");
 }
</script>

<?php
}

/***************************** StampaIntestazione *****************************/

function stampaIntestazione()
{
?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="/OSM/Anagrafe/img/favicon.ico" />
    <title>N'Tchangue - Anagrafe Web</title>
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/OSM/Anagrafe/css/style1.css">
	<link rel="stylesheet" type="text/css" href="/OSM/Anagrafe/css/utilcss.css">
	<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

</head>
<?php
}

/***************************** login *****************************/

function login()
{
    $ret=false;
    if (isset($_SESSION['loggato'])) 
    {
        if ($_SESSION['loggato']== true)
            $ret=true;
    }
    else
        $_SESSION['loggato']= false;

    return $ret;
}

/***************************** SETUP *****************************/

function setup() // invocata all'inizio di tutte le pagine, tranne login e logout
{
    // echo "entro in setup()";
    session_start(); // avvia la sessione (usa i cookie per salvare lo stato:in questo caso, per ricordarsi se l'utente è loggato)
    /*

  if (isset($_SESSION['tempo_max']))
   {
     $_SESSION['tempo_max'] = $_SERVER['REQUEST_TIME']; 
     if (time() > ($_SESSION['login_time'] + 10))
	 */
    if (isset($_SESSION['login_time']))
    {
        $time = $_SESSION['login_time']; 
        $_SESSION['login_time'] = $_SERVER['REQUEST_TIME'];
        if (time() > ($time + 300))
        {	// Passati 5 minuti, distruggi la sessione.       
            session_unset();
            session_destroy();
            //   echo "scaduto tempo di sessione: esco()";
            header("Location: /OSM/Anagrafe/index.php");
        }
    }
}


/*****************Paginazione*********************/
function unsetPag($file){		// reset variabili di sessione
    switch($file){
        case "gest_morance.php":
            unset($_SESSION['pag_c']);
		    unset($_SESSION['ord_c']);
			unset($_SESSION['campo_c']);

            unset($_SESSION['pag_p']);
			unset($_SESSION['ord_p']); 
			unset($_SESSION['campo_p']);
            break;
        case "gest_case.php":
            unset($_SESSION['pag_m']);
		    unset($_SESSION['ord_m']);
		    unset($_SESSION['campo_m']);

            unset($_SESSION['pag_p']);
		    unset($_SESSION['ord_p']);
			unset($_SESSION['campo_p']);
            break;
        case "gest_persone.php":
            unset($_SESSION['pag_m']);
		    unset($_SESSION['ord_m']);
			unset($_SESSION['campo_m']);

            unset($_SESSION['pag_c']);
		    unset($_SESSION['ord_c']);
			unset($_SESSION['campo_c']);
            break;
    }
}

/*
*** Paginazione(): ritorna la pagina che deve essere visualizzata
*** cur_page = pagina corrente
*** pagina =
*** subpag =
*** return pag: pagina da visualizzare
*/
function Paginazione($cur_page, $pagina, $subpag=null){
 //  echo "cur_page = ". $cur_page;
    if(is_null($subpag))
		$subpag=$pagina;//Se il parametro opzionale viene omesso,viene impostato al valore di $pagina
    if($cur_page !=0)
    {			//Se non è la prima volta che accedo ad una pagina
        if(isset($_SESSION[$pagina][$subpag]))
        {//Se la sessione è già impostata,l'attribuisco a $pag
            $pag=$cur_page;
            $_SESSION[$pagina][$subpag]=$pag;   
            return $pag;
        }
        else
        {//Se la sessione non è impostata
            $pag=$cur_page;
            $_SESSION[$pagina][$subpag]=$pag; 
            return $pag;
            //     echo $pag;
        }     
    }
    else
    {//Se il get non è impostato(come ad esempio quando apro per la prima volta gestione case)
        if (isset($_SESSION[$pagina][$subpag]))
        {//Se la sessione è già impostata
            $pag=$_SESSION[$pagina][$subpag];    
            return $pag;
        }else
        {//se accedo per la primissima volta alla pagina 
            $pag=1;
            $_SESSION[$pagina][$subpag]=$pag;
            return $pag;
        }
    }    
}

/************  IsLogged: controllo che l'utente loggato possa accedere alle funzionalità **********/

// Se il parametro viene passato,significa che è un utente "Utente" o "Amministratore".
// Se non viene passato viene impostato di default a NULL 
// $perm_rich = permesso utente richiesto per accedere alla funzionalità
function isLogged($perm_rich=null)
 {
//	 echo "loggato=". $_SESSION['loggato'];
//	 echo "permesso =".$_SESSION['tipo'];
//   echo "permeso richiesto=". $perm_rich;

  if(!isset($_SESSION['loggato']) || !$_SESSION['loggato'])
      header("Location: /OSM/Anagrafe/index.php");
         
  if($perm_rich == "amministratore")
	{
     if($_SESSION['tipo']== "gestore" || $_SESSION['tipo']== "utente")
        header("Location: /OSM/Anagrafe/index.php");
    }
  else
  if($perm_rich == "gestore")
   {
     if($_SESSION['tipo']=="utente")
        header("Location: /OSM/Anagrafe/index.php");
   }

 }


/***************** Alert *********************/

function alert($msg)
 {
    echo "<script type='text/javascript'>alert('$msg');</script>";
 }

function EchoMessage($msg, $redirect)
 {
    echo '<script type="text/javascript">
    alert("' . $msg . '")
    window.location.href = "'.$redirect.'"
    </script>';
 }


/***************** my_random_bytes (usato per il Salt) *********************/

 function my_random_bytes($length)
   {
        $characters = '0123456789';
        $characters_length = strlen($characters);
        $output = '';
        for ($i = 0; $i < $length; $i++)
            $output .= $characters[rand(0, $characters_length - 1)];

        return $output;
   }

/*
*** funzione che, a partire dall'id su OSM ritorna latitudine e longitudine
*** return 0  = ok
***        -1 = fail
*** utilizzo della libreria Curl (per le connessioni server to server
*** NB: deve essere attivato come parametro di configurazione PHP
*** ad es: Su altervista si deve andare nel pannello di controllo e abilitare
*** le connessioni server to server
*/

function get_latlon($id_osm, &$lat, &$lon)
{ 
// a little script check is the cURL extension loaded or not

 $url = "https://api.openstreetmap.org/api/0.6/way/".$id_osm."/full.json";

// echo "url=". $url;

 $client = curl_init($url);		// inizializzazione
 if ($client == false)
	 { 
	   echo "<br>modifica_casa.php: errore curl_init():inizializzazione con OSM non stabilita";
	   return -1;
     }
	
 curl_setopt($client,CURLOPT_RETURNTRANSFER,true);	// output come stringa

 curl_setopt($client, CURLOPT_HEADER, 0);		// non scarico header

 curl_setopt($client, CURLOPT_TIMEOUT, 20);		// set timeout

 curl_setopt($client, CURLOPT_SSL_VERIFYHOST, FALSE);

 curl_setopt($client, CURLOPT_SSL_VERIFYPEER, FALSE);

 $response = curl_exec($client);

 if ($response == false)
   { 
	 echo "<br>modifica_casa.php: errore curl_exec()esecuzione curl OSM errore";
	 return -1;
   }
 else
  {
   // echo $response;

    $arr = json_decode($response,true);
 //   var_dump($arr);
    $lat = $arr['elements'][0]['lat'];
	$lon = $arr['elements'][0]['lon'];

 //   echo "lat=".  $lat;
 //   echo "lon=".  $lon;

    curl_close($client);
  }
  return 0;
}



/*
*** funzione che, a partire da lat e lon ritorna l'id del nodo su OSM
*** return 0  = ok
***        -1 = fail
*** utilizzo della libreria Curl (per le connessioni server to server
*** NB: deve essere attivato come parametro di configurazione PHP
*** ad es: Su altervista si deve andare nel pannello di controllo e abilitare
*** le connessioni server to server
*/

function get_osm_id($lat, $lon, &$id_osm)
{ 

 $email = "acarlone@itisavogadro.it";		// per le policy di OSM bisogna identificarsi
// chiamata di API di OSM: reverse nominatim
// https://nominatim.org/release-docs/develop/api/Reverse/
 $url= "https://nominatim.openstreetmap.org/reverse?format=json";
 $url .= "&lat=".$lat."&lon=".$lon. "&zoom=18";
 $url .= "&email=".$email;

// echo "url=". $url;

 $client = curl_init($url);		// inizializzazione
 if ($client == false)
	 { 
	   echo "<br>get_osm_id(): errore curl_init():inizializzazione con OSM non stabilita";
	   return -1;
     }
	
 curl_setopt($client,CURLOPT_RETURNTRANSFER,true);	// output come stringa

 curl_setopt($client, CURLOPT_HEADER, 0);		// non scarico header

 curl_setopt($client, CURLOPT_TIMEOUT, 20);		// set timeout

 curl_setopt($client, CURLOPT_SSL_VERIFYHOST, FALSE);

 curl_setopt($client, CURLOPT_SSL_VERIFYPEER, FALSE);

 $response = curl_exec($client);

 if ($response == false)
   { 
	 echo "<br>get_osm_id(): errore curl_exec()esecuzione curl OSM errore";
	 return -1;
   }
 else
  {
//  echo $response;
    $arr = json_decode($response,true);
//    var_dump($arr);
    $id_osm = $arr['osm_id'];		// cerco 'osm_id'
 // echo "id_osm".  $id_osm;
    curl_close($client);
  }
  return 0;
}


?>