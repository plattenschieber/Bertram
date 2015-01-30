<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */

/**
 * Description of ParentView
 *
 * @author Bertram
 */
class ParentView {

    public $content;
    protected $res; //Response
    private $errors;
    private $warnings;
    private $state;

    function __construct() {
        $this->res = new stdClass();
        $this->errors = array();
        $this->warnings = array();
        $this->state = State::ERROR;
    }

    /**
     * Setzt die statischen Felder des Layouts
     */
    protected function setStaticFields() {
        $this->content->isAdminView = false;
    }

    /**
     * Funktion die einen Aufruf steuert
     */
    protected function handleRequest() {
        
    }

    /**
     * Generiert den HTML Inhalt des Feldes contentMain
     * @return string HTML des Hauptfeldes
     */
    protected function getContentMain() {
        return "";
    }

    protected function getContentTop() {
        return "";
    }

    function isAdminView() {
        return false;
    }

    public function getTemplate($filename) {

        if (file_exists($filename)) {
            return file_get_contents($filename);
        } else {
            return "";
        }
    }

    protected function genResponse() {
        $this->res->errors = $this->errors;
        $this->res->warnings = $this->warnings;
        $this->res->state = $this->state;

        header('Content-Type: text/plain; charset=utf-8');
        echo json_encode($this->res);
    }

    protected function addError($value) {
        $this->errors[] = $value;
    }

    function getState() {
        return $this->state;
    }

    function setState($state) {
        $this->state = $state;
    }

}
