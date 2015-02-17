<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
require_once ROOT . "/view/ParentView.php";
require_once ROOT . "/model/ALSConnector.php";
require_once ROOT . "/model/IS24.php";
require_once ROOT . "/model/entity/Profil.php";
require_once ROOT . "/model/entity/Advert.php";

/**
 * Description of HompePage
 *
 * @author Bertram
 */
class CallGetView extends ParentView {

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
            $this->addError(ERROR_NOUSER_EXCEPTION . "@CallGetView.php");
            return;
        }

        $profilId = filter_input(INPUT_GET, "profilId", FILTER_SANITIZE_NUMBER_INT);

        if (!Validate::isId($profilId)) {
            $profilId = 1;
        }
        //pruefe ob profilId des users oder fremde
        if (!array_key_exists($profilId, $this->user->getProfiles())) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ERROR_NO_ACCESS . "@CallGetView.php");
            return;
        }

        $this->call($profilId);
    }

    /**
     * Fuehrt anhand der Suchprofilid die Abfrage eines Katalogs aus
     * @param type $phoneId
     */
    private function call($profilId) {
        $profil = Profil::newProfil($profilId);
        $is24 = new IS24();
        $is24->search($profil);
        $als = new ALSConnector();
        $als->setHeight(640);
        $als->setWidth(920);
        $results = $als->handleJob($is24->getAdverts());
        
        $this->setState(State::SUCCESS);
        $this->res->phoneId = $this->user->getPhoneId();
        $this->res->als = $results; 
        //print_r(preg_replace('/<style.*<\/style>/', "", $results));
        //print json_encode(array('<style type="text/css">#page1.page{height:640px;width:920px;}</style>'));
    }

}
