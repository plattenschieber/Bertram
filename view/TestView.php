<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
require_once ROOT . "/view/ParentView.php";
 require_once ROOT . "/model/is24.php";

/**
 * Description of HompePage
 *
 * @author Bertram
 */
class TestView extends ParentView {

    /**
     * Erzeugt einen Homepage View
     * @param stdClass $_content Beinhaltet die Inhaltsfelder
     */
    public function process() {
        $this->handleRequest();
        $this->setStaticFields();
        ob_start();
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script>
            $(document).ready(function () {
                return;
                //Testuseredit
                $.post('/user', {phoneId: "Test21312",
                    accessToken: "xQ0hKGF86wztLR86rgekgnQzGRG4tnd2q0Gc35TqMges0won2ytKbtaH6ghg",
                    name: "test",
                    firstName: "Testfirstname",
                    sex: "f",
                    job: "sadfasdfasdf",
                    birthdate: "1987-01-24",
                    postalCode: "51373",
                    children: 5,
                    city: "Leverkusen",
                    email: "bertram-buchardt@gmx.de"}, function (data) {
                    console.log(data);
                });

                //Testuseredit
                $.post('/init', {phoneId: "Test2234312"}, function (data) {
                    console.log(data);
                });

            });
        </script>
        <?php

        $is24 = new IS24();
        $is24->search();
        print_r($is24->getAdverts());

        echo ob_get_clean();
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
    }

}
