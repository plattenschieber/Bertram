<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
require_once ROOT . "/view/ParentView.php";

/**
 * Description of HompePage
 *
 * @author Bertram
 */
class ProfilPostView extends ParentView {

    private $user;

    /**
     * Erzeugt einen Homepage View
     * @param stdClass $_content Beinhaltet die Inhaltsfelder
     */
    public function process() {
        $this->handleRequest();
        echo $this->genResponse();
    }

    /*     * @Override
     * Verarbeite POST/GET Eingaben
     * @return void
     */

    protected function handleRequest() {
        parent::handleRequest();
        $this->user = $_SESSION[obj]->getUser();
        if (!is_a($this->user, "User")) {
            $this->setState(State::ERROR);
            $this->addError(ERROR_NOUSER_EXCEPTION . "@" . filter_input(INPUT_SERVER, 'PHP_SELF'));
            return;
        }
        $this->handlePost();
    }

    private function handlePost() {

        $this->user = $_SESSION[obj]->getUser();
        if (!is_a($this->user, "User")) {
            $this->setState(State::ERROR);
            $this->addError(ERROR_NOUSER_EXCEPTION . "@ProfilPostView.php");
            return;
        }

        $profil = new Profil();
        $profil->setUserId($this->user->getId());

        $favoredStreet = filter_input(INPUT_POST, 'favoredStreet', FILTER_DEFAULT);
        $favoredArea = filter_input(INPUT_POST, 'favoredArea', FILTER_DEFAULT);
        $favoredCity = filter_input(INPUT_POST, 'favoredCity', FILTER_DEFAULT);
        $buy = filter_input(INPUT_POST, 'buy', FILTER_SANITIZE_NUMBER_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_INT);
        $balcony = filter_input(INPUT_POST, 'balcony', FILTER_DEFAULT);
        $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_NUMBER_INT);
        $rooms = filter_input(INPUT_POST, 'rooms', FILTER_SANITIZE_NUMBER_INT);



        $profil->setFavoredStreet($favoredStreet);
        $profil->setFavoredArea($favoredArea);
        $profil->setFavoredCity($favoredCity);
        $profil->setBuy($buy);
        $profil->setPrice($price);
        $profil->setBalcony($balcony);
        $profil->setSize($size);
        $profil->setRooms($rooms);

        $location = Func::getLocation($favoredStreet . " " . $favoredCity);
        $profil->setLat($location->lat);
        $profil->setLng($location->lng);
       

        $id = filter_input(INPUT_POST, 'searchProfileId', FILTER_SANITIZE_NUMBER_INT);
        if (Validate::isId($id)) {
            $profil->setId($id); //secured durch UPDATE query in Profil
        }


        if ($profil->saveToDB()) {
            $this->setState(State::SUCCESS);
            $this->res->result->profilId = $profil->getId();
            $this->res->phoneId = $this->user->getPhoneId();
        } else {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ERROR_NO_VALID_PROFIL_EXCEPTION . "@ProfilPostView");
        }

        $this->res->warnings = $profil->getWarnings();
    }

}
