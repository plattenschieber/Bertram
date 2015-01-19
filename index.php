<?php



/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */


//Definiere ROOT-Pfad zum einbinden von Dateien und Klassen
define('ROOT', preg_replace('/'.str_replace('/','\/',dirname($_SERVER['PHP_SELF'])).'$/', '', getcwd() ) );


//Pseudo Global fuer die Inhalte
$_content = new stdClass();

//Binde einen Controller ein
require_once ROOT. '/controller/DefaultCtrl.php';








