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
                }, "json");

                //Testuseredit
                $.post('/init', {phoneId: "Testuser1"}, function (data) {
                    console.log("Init:");
                    console.log(data);
                }, "json");


                //Wachted eintragen
                $.post('/watched', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    advertId: 1
                }, function (data) {
                    console.log("Angesehen speichern:");
                    console.log(data);
                }, "json");

                //Favorite eintragen
                $.post('/favourite', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    advertId: 1
                }, function (data) {
                    console.log("Favourit anlegen:");
                    console.log(data);
                }, "json");

                //Favorite entfernen
                $.post('/favourite', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    advertId: 1,
                    remove: true
                }, function (data) {
                    console.log("Favourit entfernen:");
                    console.log(data);
                }, "json");

                //Favoriten auslesen
                $.get('/favourites', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7"
                }, function (data) {
                    console.log("Favouriten auslesen:");
                    console.log(data);
                }, "json");

                //Watched auslesen
                $.get('/watched', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7"
                }, function (data) {
                    console.log("Angesehen auslesen:");
                    console.log(data);
                }, "json");
                
                //Profile auslesen
                $.get('/profiles', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7"
                }, function (data) {
                    console.log("Profileids auslesen:");
                    console.log(data);
                }, "json");
                
                /*//Profil erfassen
                $.post('/profil', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    favoredStreet: "Teststraße",
                    favoredArea: "51373",
                    favoredCity: "Leverkusen",
                    buy: 1,
                    price: 200000,
                    balcony: "Y",
                    size: 100,
                    rooms: 4
                }, function (data) {
                    console.log("Suchprofil erfassen:");
                    console.log(data);
                }, "json");*/
                
                //Profil bearbeiten
                $.post('/profil', {phoneId: "Testuser1",
                    accessToken: "2MRIc6QxKh4LBhm8Nm4hdNxh9wokNGbOOmw7zfx5ti9Tie8BBkh4bhbID2x7",
                    favoredStreet: "Dönhoffstraße",
                    favoredArea: "Wiesdorf",
                    favoredCity: "Leverkusen",
                    buy: 0,
                    price: 300000,
                    balcony: "N",
                    size: 101,
                    rooms: 5,
                    profilId: 1
                }, function (data) {
                    console.log("Suchprofil bearbeiten (ID 1):");
                    console.log(data);
                }, "json");

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
