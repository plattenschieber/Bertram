<?php

//Datenbankverbindung
define(DB_HOST, 'localhost');
define(DB_USER, 'myfh_user');
define(DB_PASS, 'nj1bK58&}laksdS123?');
define(DB_NAME, 'is2_myfh');
define(DB_CHARSET, 'utf8');




define(PERMALINK, $_SERVER['REQUEST_URI']);  //Query-URL

define(END_WITH_SLASH, false);     //Variable ob urls mit Slash enden sollen oder nicht
define(TITLE_PREFIX, '');   //Titel Praefix
define(TITLE_SUFFIX, '');   //Titel Praefix
define(HASH_KIT, 'OPasdfDFk1asdfs');    //Hash-Kit(an Passwoerter anhaengen), macht Passworthashes sicherer
define(DS, DIRECTORY_SEPARATOR);

define(MODUL, "/model/modul"); // Pfad zu den Modulen
define(CLASS_EXT, ".php"); 

define(HOME, '');

define(FROM_EMAIL, "");
define(FROM_EMAIL_NAME, "");

ini_set("session.use_trans_sid", 0);   //keine Session ids aus query-string akzeptieren
ini_set("session.use_only_cookies", 1);   //nur cookies fuer session id verwenden
date_default_timezone_set('Europe/Berlin');
setlocale(LC_ALL, 'deu', 'de_DE.UTF-8');
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');



