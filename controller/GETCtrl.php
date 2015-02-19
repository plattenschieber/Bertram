<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
$path = '/ajax';

//Verarbeitung der URL
switch (Func::path()) {


    //Cronjob Clustering alle 8 Stunden
    case( HOME . "/cron/cluster"): {
            if ($_GET[key] == "KLCLYYAOACVKSDFWDFSACBMAP") {
                //direkter Consolen aufruf des Python-scripts zur Clusterung
                echo shell_exec('python2.7 /var/www/vhosts/storyspot.de/httpdocs/myfh.storyspot.de/cgi-bin/run_cluster.py');
                break;
            }
        }

    //Cronjob Segmentierung
    case( HOME . "/cron/segment"): {
            if ($_GET[key] == "KLCLYYAOACVKSDFWDFSACBMAP") {
                //direkter Consolen aufruf des Python-scripts zur Clusterung
                echo shell_exec('python2.7 /var/www/vhosts/storyspot.de/httpdocs/myfh.storyspot.de/cgi-bin/run_rec.py 1');
                break;
            }
        }

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
            $view = new ProfilesGetView();
            $view->process($_content);

            break;
        }

    //Testseite (Browserkonsole betrachten)
    case( HOME . "/test"): {

            require_once ROOT . "/view/TestView.php";
            $view = new TestView();
            $view->process($_content);

            break;
        }

    //Abruf eines Katalogs
    case( HOME . "/call"): {

            require_once ROOT . "/view/get/CallGetView.php";
            $view = new CallGetView();
            $view->process($_content);



            break;
        }

    //sonst
    default: {

            $res = array();
            $errors = array();

            $errors[] = UNKNOWN_CALL;

            $res[state] = State::ERROR;
            $res[errors] = $errors;

            header("Status: 404 Not Found");
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($res);
        }
}