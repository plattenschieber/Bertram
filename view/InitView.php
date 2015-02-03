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
class InitView extends ParentView {

    private $phoneId;
    private $user;
    private $accessToken;

    /**
     * Erzeugt einen Homepage View
     * @param stdClass $_content Beinhaltet die Inhaltsfelder
     */
    public function process() {
        $this->handleRequest();
        $this->setStaticFields();
        echo $this->genResponse();
    }

    //@Override
    protected function setStaticFields() {
        parent::setStaticFields();
    }

    /** @Override
     * Generiert den HTML Inhalt des Feldes contentMain
     * @return string HTML des Hauptfeldes
     */
    protected function getContentTop() {
        
    }

    //@Override
    protected function handleRequest() {
        parent::handleRequest();

        $this->phoneId = filter_input(INPUT_POST, "phoneId", FILTER_SANITIZE_STRING);
        
        
        if (!isset($this->phoneId) || strlen($this->phoneId) < 5 || strlen($this->phoneId) > 50) {
            $this->addError(ERROR_NO_PHONEID);
            return;
        }

        $this->accessToken = Func::genKey(60);
        if ($this->addNewUserDB($this->phoneId, $this->accessToken)) {
            $this->setState(State::SUCCESS);
            $this->res->phoneId = $this->phoneId;
            $this->res->accessToken = $this->accessToken;
        } else {
            $this->setState(State::ERROR);
            $this->addError(ERROR_NEWUSER_EXCEPTION . "@InitView.php");
        }
    }

    /**
     * Fuegt einen neuen Benutzer in die DB ein
     * @param type $phoneId
     * @param type $accessToken
     * @return boolean
     */
    private function addNewUserDB($phoneId, $accessToken) {
        if (strlen($phoneId) == 0 || strlen($accessToken) == 0) {
            return false;
        }

        $sql = "INSERT INTO users (phoneId, accessToken, created) VALUES (?,?, NOW())";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("ss", $phoneId, $accessToken);
        $stmt->execute();

        return Func::$db->affected_rows == 1;
    }

}
