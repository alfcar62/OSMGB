<?php
/******************* HTML *************************/
//Data ultima modifica:27/02/2020
//Descrizione:Implementazione della gestione multilingue attraverso un file .json Autore:Gobbi Dennis
//Descrizione:Gestione degli utenti Autore:Ferraiuolo Pasquale
//25/03/2020: Ferraiuolo: Aggiunta menu a tendina e nome utente nella videata
?>

<link rel="stylesheet" type="text/css" href="/OSM/Anagrafe/css/utilcss.css">
<?php
function stampaNavbar()
{
    //echo getcwd();

    $lang=isset($_SESSION['lang'])?$_SESSION['lang']:"ITA"; //Se nessuna lingua è stata scelta,verrà messa come default quella italiana
    $lang= strtoupper($lang);
    $jsonFile=file_get_contents(__DIR__ ."/gestione_lingue/translations.json");//Converto il file json in una stringa
    $jsonObj=json_decode($jsonFile);//effettuo il decode della stringa json e la salvo in un oggetto
?>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<ul>
 <li class="titolo"><a href="/OSM/Anagrafe/index.php" class="nav-item is-active"><div id="T1">Ntachangue</div></a></li>
        <link rel="stylesheet" type="text/css" href="/OSM/Anagrafe/css/style1.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


        <?php
    if (login())
    {
        if($_SESSION['tipo']!="utente"){
        ?>   
    <li><a href="/OSM/Anagrafe/morance/gest_morance.php" class="nav-item is-active">Moran&ccedil;e</a></li>
    <li><a href="/OSM/Anagrafe/case/gest_case.php" class="nav-item is-active"><?php echo ($jsonObj->{$lang."Navbar"}[4])?></a></li><!--Case --> 
    <li><a href="/OSM/Anagrafe/persone/gest_persone.php" class="nav-item is-active"><?php echo ($jsonObj->{$lang."Navbar"}[5])?></a></li><!--Persone --> 
    <!--   <li><a href="/OSM/Anagrafe/OSM/index.html" target="catasto"><?php echo $jsonObj->{$lang."Navbar"}[6]."<br>".$jsonObj->{$lang."Navbar"}[7] ?></a></li> --Mappa Catastale --> 
    <li><a href="https://www.openstreetmap.org/search?query=ntchangue#map=16/12.0039/-15.5081" target="osm" class="nav-item is-active">OSM</a></li>


    <?php
        } ?>

     <li><a href="/OSM/Anagrafe/stat/statistiche.php" class="nav-item is-active"><?php echo ($jsonObj->{$lang."Navbar"}[8])?><?php echo ($jsonObj->{$lang."Navbar"}[9])?></a></li><!--Report statistiche --> 
    <?php  if($_SESSION['tipo']=="admin"){
            echo "<li><a href='/OSM/Anagrafe/utenti/gestione_utenti.php' class='nav-item is-active'>".$jsonObj->{$lang."Navbar"}[10]."</a></li>";//Gestione Utenti
            //			echo "<li><a href='/OSM/Anagrafe/utility.php'>".$jsonObj->{$lang."Navbar"}[11]."</a></li>";
            echo "<li><a href='https://drive.google.com/file/d/1VOXNtxo_ULb5xbqlJeVmjNz9vhz2insi/view?usp=sharing' target=new class='nav-item is-active'>Segnalazioni</a></li>";
            ?>  

          
                <?php
        }  ?>
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
            Lingue
        </a>
    </li>
    <div id="dropMenu">
        <!--Il tag option del select non supporta le img,ho optato quindi per la rimozione di un form e al posto di esso ho messo dei link con href una pagina php con richiesta get -->
        <a href="/OSM/Anagrafe/gestione_lingue/gest_lingue.php?lang=EN&dir=<?php echo getcwd();?>" >
          <img class="ling"  src="/OSM/Anagrafe/gestione_lingue/en_flag.png"  WIDTH="40" HEIGHT="20" BORDER="0" class="flag" alt="EN">
        </a><br>
        <a href="/OSM/Anagrafe/gestione_lingue/gest_lingue.php?lang=ITA&dir=<?php echo getcwd();?>"   >
             <img class="ling" src="/OSM/Anagrafe/gestione_lingue/ita_flag.png"  WIDTH="40" HEIGHT="20" BORDER="0" class="flag" alt="ITA">
        </a>

    </div>


    <?php

    }
    else
    {
    ?>
    <li><a href="/OSM/Anagrafe/info/chisiamo.php"><?php echo ($jsonObj->{$lang."Navbar"}[0])?></a></li><!--Chi siamo --> 
    <li><a href="/OSM/Anagrafe/info/progetto.php"><?php echo ($jsonObj->{$lang."Navbar"}[1])?></a></li><!--Il progetto --> 
    <li><a href="/OSM/Anagrafe/login.php"><?php echo ($jsonObj->{$lang."Navbar"}[2])?><IMG SRC="/OSM/Anagrafe/img/ico-login.png" WIDTH="36" HEIGHT="33" BORDER="0" ALT="Entra"></a></li> <!--Entra --> 


        <!--Vecchio select per la lingua
<form action="/OSM/Anagrafe/gestione_lingue/gestione_lingue.php" method="post">
<select name="lang" id="sel" onchange="this.form.submit()">
<option value=" "></option>
<option value="ITA">Italiano</option>
<option value="EN">Inglese</option>
</select>
</form>
-->         

    <li>
        <a href="#" onclick="myFx()" class="globe">
            <img src="/OSM/Anagrafe/gestione_lingue/output-onlinepngtools.png" WIDTH='36' HEIGHT='33' BORDER='0' ALT="LANG" class="globe">
        </a>
    </li>
    <div id="dropMenu">
        <!--Il tag option del select non supporta le img,ho optato quindi per la rimozione di un form e al posto di esso ho messo dei link con href una pagina php con richiesta get -->
        <a href="/OSM/Anagrafe/gestione_lingue/gest_lingue.php?lang=EN&dir=<?php echo getcwd();?>" >
            <img src="/OSM/Anagrafe/gestione_lingue/en_flag.png" class="flag" alt="EN">
        </a><br>
       <a href="/OSM/Anagrafe/gestione_lingue/gest_lingue.php?lang=EN&dir=<?php echo getcwd();?>" >
            <img src="/OSM/Anagrafe/gestione_lingue/en_flag.png" WIDTH="40" HEIGHT="20" BORDER="0" class="flag" alt="EN">
        </a><br>
        <a href="/OSM/Anagrafe/gestione_lingue/gest_lingue.php?lang=ITA&dir=<?php echo getcwd();?>">
            <img src="/OSM/Anagrafe/gestione_lingue/ita_flag.png"  WIDTH="40" HEIGHT="20" BORDER="0" class="flag" alt="ITA">
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
<?php
}
function stampaIntestazione()
{
?>
<head>
    <link rel="shortcut icon" type="image/x-icon" href="/OSM/Anagrafe/img/favicon.ico" />
    <title>Ntchangue - Anagrafe Web</title>
    <link rel="stylesheet" type="text/css" href="/OSM/Anagrafe/css/style1.css">
    <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<?php
}


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
function unsetPag($file){ 
    switch($file){
        case "gest_morance.php":
            unset($_SESSION['pag_c']);
            unset($_SESSION['pag_p']);
            break;
        case "gest_case.php":
            unset($_SESSION['pag_m']);
            unset($_SESSION['pag_p']);
            break;
        case "gest_persone.php":
            unset($_SESSION['pag_m']);
            unset($_SESSION['pag_c']);
            break;
    }
}


/***************************** SLERT *****************************/


function alert($msg) {
    echo "<script type='text/javascript'>alert('$msg');</script>";
}

function EchoMessage($msg, $redirect)
{
    echo '<script type="text/javascript">
 alert("' . $msg . '")
 window.location.href = "'.$redirect.'"
 </script>';
}

?>