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

        $profileId = filter_input(INPUT_GET, "searchProfileId", FILTER_SANITIZE_NUMBER_INT);
        $width = filter_input(INPUT_GET, "width", FILTER_SANITIZE_NUMBER_INT);
        $height = filter_input(INPUT_GET, "height", FILTER_SANITIZE_NUMBER_INT);


        //pruefe ob profilId des users oder fremde
        if (!array_key_exists($profileId, $this->user->getProfiles())) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ERROR_NO_ACCESS . "@CallGetView.php");
            return;
        }

        $this->call($profileId, $width, $height);
    }

    /**
     * Fuehrt anhand der Suchprofilid die Abfrage eines Katalogs aus
     * @param int $profileId ID eines searchProfiles
     * @param int width Aufloesung Breite
     * @param ind height Aufloesung Hoehe
     */
    private function call($profileId, $width, $height) {
        $profil = Profil::newProfil($profileId);
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



        $results = $this->getResults($is24->getAdverts(), $profileId, $als);
        //Falls keine Adverts gefunden wurden
        if (!$results || count($results) == 0) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(NO_ADVERTS_FOUND . "@CallGetView.php");
            return;
        }
       

        $alsJSON = json_decode($results);
        //print_r($results);
        //Falls Json response von ALS nicht geparsed werden konnte
        if (json_last_error() != JSON_ERROR_NONE) {
            $this->setState(State::ERROR);
            $this->res->phoneId = $this->user->getPhoneId();
            $this->addError(ALS_JSON_PARSE_ERROR . "@CallGetView.php");
            print_r($results);
            return;
        }

        $this->setState(State::SUCCESS);
        $this->res->phoneId = $this->user->getPhoneId();
        $this->res->als = $alsJSON;

        //print json_encode(array('<style type="text/css">#page1.page{height:640px;width:920px;}</style>'));
    }

    private function getResults($adverts, $profileId, $als) {
        //Segmentierung durchfuehren
        @shell_exec('python2.7 /var/www/vhosts/storyspot.de/httpdocs/myfh.storyspot.de/cgi-bin/run_rec.py ' . $profileId);


        $results = array();
        $maxAdverts = MAX_ADVERTS;
        //Ermittle passende Adverts die bereits in der DB sind
        $sql = "SELECT RS_searchProfiles_adverts.advertId, "
                . "RS_searchProfiles_adverts.priority "
                . "FROM RS_searchProfiles_adverts "
                . "WHERE "
                . "RS_searchProfiles_adverts.searchProfileId = ? "
                . "AND RS_searchProfiles_adverts.advertId NOT IN (SELECT advertId FROM showed WHERE userId = ? ) "
                . "ORDER BY -priority DESC "
                . "LIMIT ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("iii", $profileId, $this->user->getId(), $maxAdverts);
        $stmt->execute();

        $stmt->store_result();
        $stmt->bind_result($id, $priority);

        //speichere die gesehenen Anzeigen
        $sql2 = "INSERT INTO showed (userId, advertId) VALUE (?,?)";
        $stmt2 = Func::$db->prepare($sql2);



        while ($stmt->fetch()) {
            $advert = Advert::newAdvert($id);
            if (is_a($advert, "Advert")) {
                $advert->setPriority($priority);
                $results[] = $advert;

                $stmt2->bind_param("ii", $this->user->getId(), $advert->getId());
                $stmt2->execute();
            }
        }
        //falls keine gefunden gib leeres Array zurueck
        if (!$results || count($results) == 0) {
            return array();
        }
        return $als->handleJob($results);
    }

}
