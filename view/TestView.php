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
                //return;
                //Testuseredit
                $.post('/user', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    name: "test",
                    firstName: "Testfirstname",
                    sex: "f",
                    job: "sadfasdfasdf",
                    birthdate: "1987-01-24",
                    postalCode: "51373",
                    children: 5,
                    city: "Leverkusen",
                    email: "bertram-buchardt@gmx.de"}, function (data) {
                    console.log("Update user:");
                    console.log(data);
                });

                //Testuseredit
                $.post('/init', {phoneId: "Testuser1"}, function (data) {
                    console.log("Init:");
                    console.log(data);
                });


                //Wachted eintragen
                $.post('/watched', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    advertId: 1
                }, function (data) {
                    console.log("Angesehen speichern:");
                    console.log(data);
                });

                //Favorite eintragen
                $.post('/favourite', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    advertId: 1
                }, function (data) {
                    console.log("Favourit anlegen:");
                    console.log(data);
                });

                //Favorite entfernen
                $.post('/favourite', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    advertId: 1,
                    remove: true
                }, function (data) {
                    console.log("Favourit entfernen:");
                    console.log(data);
                });

                //Favoriten auslesen
                $.get('/favourites', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7"
                }, function (data) {
                    console.log("Favouriten auslesen:");
                    console.log(data);
                });

                //Watched auslesen
                $.get('/watched', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7"
                }, function (data) {
                    console.log("Angesehen auslesen:");
                    console.log(data);
                });

            });
        </script>
        <?php

        $is24 = new IS24();
        //$is24->search();
        //print_r($is24->getAdverts());

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
