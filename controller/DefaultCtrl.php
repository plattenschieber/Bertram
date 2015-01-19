<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */


error_reporting(E_ALL ^ E_NOTICE);
/* Einfachste und minimalistische Controller-Variante */


//Basisdateien einbinden
require_once ROOT . '/config/config.inc';
require_once ROOT . '/config/strings.php';

require_once ROOT . '/model/core/Func.inc';
require_once ROOT . '/model/core/Validate.php';
require_once ROOT . '/model/core/Enum.php';
require_once ROOT . '/model/core/State.php';

require_once ROOT . '/model/sys/Session.php';



//Session starten
session_start();


//fuer die Entwicklung
if (filter_input(INPUT_GET, 'key') === 'unikoeln') {
    $_SESSION[log] = 1;
    header('Location: ' . HOME . '/');
}


if ($_SESSION[log] != 1) {
    die('Kein Zugriff');
}


//Datenbankverbindung init
Func::DBini();

//Init Session Object if needed
if (!isset($_SESSION[obj]) || !is_a($_SESSION[obj], 'Session')) {
    $_SESSION[obj] = new Session();
}


$_SESSION[obj]->auth();

/**
 * ########
 * In Mehrere Controller teilen fuer verschiedene Bereiche /admin/...  /veranstalter...
 * /veranstaltung/...
 * 
 * ############ 
 */
do {

    if (filter_input(INPUT_SERVER, 'REQUEST_METHODE') === "POST") {
        require_once ROOT . '/controller/POSTCtrl.php';
        break;
    }

    if (filter_input(INPUT_SERVER, 'REQUEST_METHODE') === "GET") {
        
        require_once ROOT . '/controller/GETCtrl.php';
        break;
    }


    $res = array();
    $errors = array();

    $errors[] = UNKNOWN_CALL;

    $res[state] = State::ERROR;
    $res[errors] = $errors;

    header("Status: 404 Not Found");
    header('Content-Type: text/plain; charset=utf-8');
    echo json_encode($res);

    break;
} while (false);




