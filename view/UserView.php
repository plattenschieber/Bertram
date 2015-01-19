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
class UserView extends ParentView {
    
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
    }

    protected function setStaticFields() {
        parent::setStaticFields();
    }

    /** @Override
     * Generiert den HTML Inhalt des Feldes contentMain
     * @return string HTML des Hauptfeldes
     */
    protected function getContentTop() {
        
    }

    protected function handleRequest() {
        parent::handleRequest();
        
        
        
    }

}
