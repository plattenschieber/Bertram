<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
$path = '';

//Verarbeitung der URL
switch (Func::path()) {


    //Erste Anmeldung
    case( HOME . "/init"): {

            require_once ROOT . "/view/InitView.php";
            $view = new InitView();
            $view->process($_content);

            break;
        }

    //User Daten aendern
    case( HOME . "/user"): {

            require_once ROOT . "/view/UserPostView.php";
            $view = new UserPostView();
            $view->process($_content);

            break;
        }


    default: {

            $res = array();
            $errors = array();
            $warnings = array();

            $errors[] = UNKNOWN_CALL;

            $res[state] = State::ERROR;
            $res[errors] = $errors;
            $res[warnings] = $warnings;

            header("Status: 404 Not Found");
            header('Content-Type: text/plain; charset=utf-8');
            echo json_encode($res);
        }
}