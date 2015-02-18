<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */

/**
 * Description of Profile
 *
 * @author Bertram
 */
class Profil {

    private $id;
    private $userId;
    private $favoredStreet;
    private $favoredArea;
    private $favoredCity;
    private $buy;
    private $price;
    private $balcony;
    private $lat;
    private $lng;
    private $size;
    private $rooms;
    private $warnings;

    /**
     * Konstruktor
     */
    function __construct() {
        $this->warnings = array();
    }

    /**
     * Static Konstruktur: zum erzeugen eines Users anhand der Id
     * @param int $id
     * @return \User
     */
    public static Function newProfil($id) {
        $self = new Profil();
        $self->setId($id);
        if ($self->loadFromDB()) {
            return $self;
        }
        return null;
    }

    /**
     * Lade die Daten aus der Datenbank in das User-Objekt
     * @param boolean $phoneId
     * @return boolean
     */
    public function loadFromDB() {
        $sql = "SELECT searchProfiles.id, "
                . "searchProfiles.userId, "
                . "searchProfiles.favoredStreet, "
                . "searchProfiles.favoredArea, "
                . "searchProfiles.favoredCity, "
                . "searchProfiles.buy, "
                . "searchProfiles.price, "
                . "searchProfiles.balcony, "
                . "searchProfiles.size, "
                . "searchProfiles.rooms, "
                . "searchProfiles.lat, "
                . "searchProfiles.lng "
                . "FROM searchProfiles WHERE searchProfiles.id = ? ";

        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 1) {
            return false;
        }

        $stmt->bind_result($this->id, $this->userId, $this->favoredStreet, $this->favoredArea, $this->favoredCity, $this->buy, $this->price, $this->balcony, $this->size, $this->rooms, $this->lat, $this->lng);
        $stmt->fetch();

        return true;
    }

    function saveToDB() {
        if (isset($this->id) && Validate::isId($this->id)) {
            return $this->updateDB();
        } else {
            return $this->insertDB();
        }
    }

    function updateDB() {
        if (!$this->isValid()) {
            return false;
        }
        $sql = "UPDATE searchProfiles SET "
                . "favoredStreet = ?, "
                . "favoredArea = ?, "
                . "favoredCity = ?, "
                . "buy = ?, "
                . "price = ?, "
                . "balcony = ?, "
                . "size = ?, "
                . "rooms = ?, "
                . "lat = ?,"
                . "lng = ? "
                . "WHERE id = ? "
                . "AND userId = ? "
                . "LIMIT 1";

        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("sssiisiiddii", $this->favoredStreet, $this->favoredArea, $this->favoredCity, $this->buy, $this->price, $this->balcony, $this->size, $this->rooms, $this->lat, $this->lng, $this->id, $this->userId);
        $stmt->execute();

        if (Func::$db->affected_rows != 1) {
            $this->warnings["update"] = NO_UPDATE_CHANGE . "@Profil";
        } else {
            //loesche verbinund aus Anzeigen und Suchprofil da ggf nicht mehr passend
            $sql2 = "DELETE FROM RS_searchProfiles_adverts WHERE searchProfileId = ?";
            $stmt2 = Func::$db->prepare($sql2);
            $stmt2->bind_param("i", $this->id);
            $stmt2->execute();
        }

        return true;
    }

    function insertDB() {
        if (!$this->isValid()) {
            return false;
        }
        $sql = "INSERT INTO searchProfiles ( "
                . "userId, "
                . "favoredStreet, "
                . "favoredArea, "
                . "favoredCity, "
                . "buy, "
                . "price, "
                . "balcony, "
                . "size, "
                . "rooms,"
                . "lat,"
                . "lng ) "
                . "VALUES (?,?,?,?,?,?,?,?,?,?,?) ";

        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("isssiisiidd", $_SESSION[obj]->getUser()->getId(), $this->favoredStreet, $this->favoredArea, $this->favoredCity, $this->buy, $this->price, $this->balcony, $this->size, $this->rooms, $this->lat, $this->lng);
        $stmt->execute();

        if (Func::$db->affected_rows == 1) {
            $this->id = Func::$db->insert_id;
            return true;
        } else {
            $this->warnings['system'] = 'Fehler: #Insert-1@Profil';
            return false;
        }
    }

    function isValid() {
        //reset
        $this->warnings = array();

        $valid = true;

        if (!$this->checkBasicFields()) {
            $valid = false;
        }

        if (!$this->checkExtraFields()) {
            $valid = false;
        }

        return $valid;
    }

    function checkBasicFields() {
        $valid = true;

        if (isset($this->id) && !Validate::isId($this->id)) {
            $valid = false;
            $this->warnings["id"] = NO_VALIDID . "@Profil";
        }

        if (!Validate::isId($this->userId)) {
            $valid = false;
            $this->warnings["userId"] = NO_VALIDID . "@Profil";
        }


        return $valid;
    }

    function checkExtraFields() {
        $valid = true;

        if (strlen($this->favoredStreet) > 50) {
            $valid = false;
            $this->warnings["favoredStreet"] = INPUT_TOO_LONG . "@Profil";
        }

        if (strlen($this->favoredCity) > 50) {
            $valid = false;
            $this->warnings["favoredCity"] = INPUT_TOO_LONG . "@Profil";
        }

        if (strlen($this->favoredArea) > 50) {
            $valid = false;
            $this->warnings["favoredArea"] = INPUT_TOO_LONG . "@Profil";
        }

        if (strlen($this->buy) > 0 && !is_numeric($this->buy)) {
            $valid = false;
            $this->warnings["buy"] = NO_VALID_INTEGER . "@Profil";
        }

        if (strlen($this->price) > 0 && !is_numeric($this->price)) {
            $valid = false;
            $this->warnings["price"] = NO_VALID_INTEGER . "@Profil";
        }

        if (strlen($this->size) > 0 && !is_numeric($this->size)) {
            $valid = false;
            $this->warnings["size"] = NO_VALID_INTEGER . "@Profil";
        }

        if (strlen($this->balcony) > 0 && $this->balcony !== "Y" && $this->balcony !== "N") {
            $valid = false;
            $this->warnings["balcony"] = NO_VALID_VALUE . "@Profil";
        }

        return $valid;
    }

    function toArray() {
        $properties = get_object_vars($this);
        return $properties;
    }

    function getId() {
        return $this->id;
    }

    function getUserId() {
        return $this->userId;
    }

    function getFavoredStreet() {
        return $this->favoredStreet;
    }

    function getFavoredArea() {
        return $this->favoredArea;
    }

    function getFavoredCity() {
        return $this->favoredCity;
    }

    function getLat() {
        return $this->lat;
    }

    function getLng() {
        return $this->lng;
    }

    function setLat($lat) {
        $this->lat = $lat;
    }

    function setLng($lng) {
        $this->lng = $lng;
    }

    function getBuy() {
        return $this->buy;
    }

    function getPrice() {
        return $this->price;
    }

    function getBalcony() {
        return $this->balcony;
    }

    function getSize() {
        return $this->size;
    }

    function getRooms() {
        return $this->rooms;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function setFavoredStreet($favoredStreet) {
        $this->favoredStreet = $favoredStreet;
    }

    function setFavoredArea($favoredArea) {
        $this->favoredArea = $favoredArea;
    }

    function setFavoredCity($favoredCity) {
        $this->favoredCity = $favoredCity;
    }

    function setBuy($buy) {
        $this->buy = $buy;
    }

    function setPrice($price) {
        $this->price = $price;
    }

    function setBalcony($balcony) {
        $this->balcony = $balcony;
    }

    function setSize($size) {
        $this->size = $size;
    }

    function setRooms($rooms) {
        $this->rooms = $rooms;
    }

    function getWarnings() {
        return $this->warnings;
    }

}
