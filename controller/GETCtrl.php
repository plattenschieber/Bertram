<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
$path = '/ajax';

//Verarbeitung der URL
switch (Func::path()) {





    //Benutzerdaten abfragen
    case( HOME . "/user"): {

            require_once ROOT . "/view/get/UserGetView.php";
            $view = new UserGetView();
            $view->process($_content);

            break;
        }

    //Angesehen abfragen
    case( HOME . "/watched"): {

            require_once ROOT . "/view/get/WatchedGetView.php";
            $view = new WatchedGetView();
            $view->process($_content);

            break;
        }

    //Favouriten abfragen
    case( HOME . "/favourites"): {

            require_once ROOT . "/view/get/FavouriteGetView.php";
            $view = new FavouriteGetView();
            $view->process($_content);

            break;
        }

    //Suchprofil abfragen
    case( HOME . "/profiles"): {

            require_once ROOT . "/view/get/ProfilesGetView.php";
            $view = new ProfilGetView();
            $view->process($_content);

            break;
        }

    //
    case( HOME . "/test"): {

            require_once ROOT . "/view/TestView.php";
            $view = new TestView();
            $view->process($_content);

            break;
        }


    case( HOME . "/call"): {

            require_once ROOT . "/model/ALSConnector.php";

            $als = new ALSConnector();
            print_r($als->handleJob(file_get_contents(ROOT . "/Example.xml")));

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