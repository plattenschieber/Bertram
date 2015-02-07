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
class FavouritePostView extends ParentView {

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
            $this->addError(ERROR_NOUSER_EXCEPTION . "@FavouritePostView");
            return;
        }
        $this->handlePost();
    }

    private function handlePost() {

        $advertId = filter_input(INPUT_POST, 'advertId', FILTER_SANITIZE_NUMBER_INT);
        $remove = (filter_input(INPUT_POST, 'remove', FILTER_SANITIZE_STRING) == "true") ? true : false;

        if ($remove) {
            
            $this->removeFavourite($advertId);
        } else {
            $this->addFavourite($advertId);
        }
    }

    private function addFavourite($advertId) {
        switch ($this->user->addAdvertFavourite($advertId)) {
            case(1): { //Erfolg
                    $this->setState(State::SUCCESS);
                    $this->res->phoneId = $this->user->getPhoneId();
                    break;
                }
            case(0): { //advertId nicht gefunden
                    $this->setState(State::ERROR);
                    $this->res->phoneId = $this->user->getPhoneId();
                    $this->addError(ERROR_ADVERT_ID_NOT_FOUND . "@FavouritePostView");
                    break;
                }
            case(2): {//Fehler beim schreiben in die Datenbank
                    $this->setState(State::ERROR);
                    $this->res->phoneId = $this->user->getPhoneId();
                    $this->addError(ERROR_INSERT_FAIL . "@FavouritePostView");
                    break;
                }
        }
    }

    private function removeFavourite($advertId) {
        switch ($this->user->removeAdvertFavourite($advertId)) {
            case(1): { //Erfolg
                    $this->setState(State::SUCCESS);
                    $this->res->phoneId = $this->user->getPhoneId();
                    break;
                }
            case(0): { //advertId nicht gefunden
                    $this->setState(State::ERROR);
                    $this->res->phoneId = $this->user->getPhoneId();
                    $this->addError(ERROR_ADVERT_ID_NOT_FOUND . "@FavouritePostView");
                    break;
                }
            case(2): {//Fehler beim Loeschen
                    $this->setState(State::ERROR);
                    $this->res->phoneId = $this->user->getPhoneId();
                    $this->addError(ERROR_DELETE_FAIL . "@FavouritePostView");
                    break;
                }
        }
    }

}
