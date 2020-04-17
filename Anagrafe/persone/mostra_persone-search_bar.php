<?php
//Data ultima modifica:29/02/2020   Autore:Gobbi Dennis
//Descrizione:Per la barra di ricerca in gest_persone.php(attualmente non funzionante)
  session_start();
  isLogged("utente");
  $_SESSION["search-bar"]=$_POST["nome"];
  ?>