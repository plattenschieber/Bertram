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
        $width = filter_input(INPUT_GET, "width", FILTER_SANITIZE_NUMBER_INT);
        $height = filter_input(INPUT_GET, "height", FILTER_SANITIZE_NUMBER_INT);

        if (!Validate::isId($profilId)) {                                           //todo: remove
            $profilId = 1; //zur entwicklung und Tests
        }
        //pruefe ob profilId des users oder fremde
        if (!array_key_exists($profilId, $this->user->getProfiles())) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ERROR_NO_ACCESS . "@CallGetView.php");
            return;
        }

        $this->call($profilId, $width, $height);
    }

    /**
     * Fuehrt anhand der Suchprofilid die Abfrage eines Katalogs aus
     * @param int $profilId ID eines searchProfiles
     * @param int width Aufloesung Breite
     * @param ind height Aufloesung Hoehe
     */
    private function call($profilId, $width, $height) {
        $profil = Profil::newProfil($profilId);
        //max/min filtern
        if ($width < 200 || $width > 2000) {
            $width = 920;
        }
        //max/min filtern
        if ($height < 100 || $height > 1800) {
            $height = 920;
        }

        $is24 = new IS24();
        $is24->search($profil);
        $als = new ALSConnector();
        $als->setHeight($width);
        $als->setWidth($height);

        //Falls Profil keine Geocoordinaten hat
        if (strlen($profil->getLat()) == 0 || strlen($profil->getLng()) == 0) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(NO_VALID_SEARCH_ADDRESS . "@CallGetView.php");
            return;
        }
        //Falls keine Adverts gefunden wurden
        if (!$is24->getAdverts() || count($is24->getAdverts()) == 0) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(NO_ADVERTS_FOUND . "@CallGetView.php");
            return;
        }

        $results = $als->handleJob($is24->getAdverts());

        $alsJSON = json_decode($results); 
        //print_r($results);
        //Falls Json response von ALS nicht geparsed werden konnte
        if (json_last_error() != JSON_ERROR_NONE) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ALS_JSON_PARSE_ERROR . "@CallGetView.php");
            return;
        }

        $this->setState(State::SUCCESS);
        $this->res->phoneId = $this->user->getPhoneId();
        $this->res->als = $alsJSON;
      
        //print json_encode(array('<style type="text/css">#page1.page{height:640px;width:920px;}</style>'));
    }

}
