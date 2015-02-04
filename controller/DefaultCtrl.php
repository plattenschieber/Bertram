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




//Datenbankverbindung init
Func::DBini();

//Init Session Object if needed
if (!isset($_SESSION[obj]) || !is_a($_SESSION[obj], 'Session')) {
    $_SESSION[obj] = new Session();
}


if (Func::path() != "/init" && Func::path() != "/test" && !$_SESSION[obj]->auth()) {
    $res = array();
    $errors = array();
    $warnings = array();

    $errors[] = VALIDATION_ERROR;

    $res[state] = State::ERROR;
    $res[errors] = $errors;
    $res[warnings] = $warnings;

    
    header('Content-Type: text/plain; charset=utf-8');
    echo json_encode($res);
    die();
}

/**
 * ########
 * In Mehrere Controller teilen fuer verschiedene Bereiche /admin/...  /veranstalter...
 * /veranstaltung/...
 * 
 * ############ 
 */
do {

    if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === "POST") {
        require_once ROOT . '/controller/POSTCtrl.php';
        break;
    } else {
        require_once ROOT . '/controller/GETCtrl.php';
        break;
    }
} while (false);




