<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
$path = '/ajax';

//Verarbeitung der URL
switch (Func::path()) {


    


    //
    case( HOME . "/user"): {

            require_once ROOT . "/view/UserView.php";
            $view = new UserView();
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