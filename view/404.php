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
class NotFoundView extends ParentView {

    /**
     * Erzeugt einen Homepage View
     * @param stdClass $_content Beinhaltet die Inhaltsfelder
     */
    public function process($_content) {
        $this->content = $_content;
        $this->setStaticFields();
        $this->handleRequest();


        require_once ROOT . '/view/template/404tpl.php';
    }

    protected function setStaticFields() {
        parent::setStaticFields();
        $this->content->bodyClass = "page-404";
    }

    /** @Override
     * Generiert den HTML Inhalt des Feldes contentMain
     * @return string HTML des Hauptfeldes
     */
    protected function getContentMain() {

        return $this->getContentMainSub();
    }

    private function getContentMainSub() {
        ob_start();
        ?>
        <div class="wrapper">
            <img id="logo" src="/img/logo-white.png">
            <p>
                <a href="/" class="btn btn-danger btn-lg">Startseite</a> 
                
                <a href="javascript:history.back();" class="btn btn-danger btn-lg">zur vorherigen Seite</a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function handleRequest() {
        parent::handleRequest();
    }

}
