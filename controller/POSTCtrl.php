<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
$path = '/ajax';

//Verarbeitung der URL
switch (Func::path()) {


    //Erste Anmeldung
    case( HOME . "/init"): {

            require_once ROOT . "/view/InitView.php";
            $view = new InitView();
            $view->process($_content);

            break;
        }


    default: {

            $res = array();
            $errors = array();

            $errors[] = UNKNOWN_CALL;

            $res[state] = State::ERROR;
            $res[errors] = $errors;

            header("Status: 404 Not Found");
            header('Content-Type: text/plain; charset=utf-8');
            echo json_encode($res);
        }
}