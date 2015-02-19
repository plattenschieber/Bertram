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
class ProfilGetView extends ParentView {

    private $user;

    /**
     * Erzeugt einen Homepage View
     * @param stdClass $_content Beinhaltet die Inhaltsfelder
     */
    public function process() {
        $this->handleRequest();
        echo $this->genResponse();
    }

    /** @Override
     * Verarbeite POST/GET Eingaben
     * @return void
     */
    protected function handleRequest() {
        parent::handleRequest();
        $this->user = $_SESSION[obj]->getUser();
        if (!is_a($this->user, "User")) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ERROR_NOUSER_EXCEPTION . "@UserGetView.php");
            return;
        }

        $profilId = filter_input(INPUT_GET, "searchProfileId", FILTER_SANITIZE_NUMBER_INT);
        
         //pruefe ob profilId des users oder fremde
        if (!array_key_exists($profilId, $this->user->getProfiles())) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ERROR_NO_ACCESS . "@UserGetView.php");
            return;
        }

        $this->setState(State::SUCCESS);
        $this->res->phoneId = $this->user->getPhoneId();
        $this->res->result = $this->user->getProfil($profilId);
    }

}
