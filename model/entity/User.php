<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */

/**
 * Description of User
 *
 * @author Bertram
 */
class User {

    private $userId;
    private $phoneId;
    private $name;
    private $firstName;
    private $sex;
    private $job;
    private $accessToken;
    private $birthdate;
    private $postalCode;
    private $children;
    private $city;
    private $lat;
    private $lng;
    private $email;
    private $created;
    private $warnings;

    /**
     * Konstruktor
     */
    function __construct() {
        $this->warnings = array();
    }

    /**
     * Static Konstruktur: zum erzeugen eines Users anhand der phoneId
     * @param string $phoneId
     * @return \User
     */
    public static Function newPhoneUser($phoneId) {
        $self = new User();
        $self->setPhoneId($phoneId);
        if ($self->loadFromDB(true)) {
            return $self;
        }
        return null;
    }

    /**
     * Static Konstruktur: zum erzeugen eines Users anhand der Id
     * @param int $id
     * @return \User
     */
    public static Function newUser($id) {
        $self = new User();
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
    public function loadFromDB($phoneId = false) {
        $sql = "SELECT users.id, "
                . "users.phoneId, "
                . "users.name, "
                . "users.firstName, "
                . "users.sex, "
                . "users.job, "
                . "users.accessToken, "
                . "users.birthdate, "
                . "users.postalCode, "
                . "users.city, "
                . "users.email, "
                . "users.created, "
                . "users.children,"
                . "users.lat,"
                . "users.lng "
                . "FROM users WHERE ";
        if ($phoneId) {
            $sql.= "users.phoneId = ? ";
        } else {
            $sql.= "users.id = ? ";
        }

        $stmt = Func::$db->prepare($sql);
        if ($phoneId) {
            $stmt->bind_param('s', $this->phoneId);
        } else {
            $stmt->bind_param('i', $this->userId);
        }

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows != 1) {
            return false;
        }

        $stmt->bind_result($this->userId, $this->phoneId, $this->name, $this->firstName, $this->sex, $this->job, $this->accessToken, $this->birthdate, $this->postalCode, $this->city, $this->email, $this->created, $this->children, $this->lat, $this->lng);
        $stmt->fetch();

        return true;
    }

    function saveToDB() {
        if (!$this->isValid()) {
            return false;
        }

        $sql = "UPDATE users SET "
                . "name = ?, "
                . "firstName = ?, "
                . "sex = ?, "
                . "job = ?, "
                . "birthdate = ?, "
                . "postalCode = ?, "
                . "children = ?, "
                . "city = ?, "
                . "email = ?,"
                . "lat = ?,"
                . "lng = ? "
                . "WHERE id = ? "
                . "LIMIT 1";

        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("sssssiissddi", $this->name, $this->firstName, $this->sex, $this->job, $this->birthdate, $this->postalCode, $this->children, $this->city, $this->email,$this->lat, $this->lng, $this->userId);
        $stmt->execute();

        if (Func::$db->affected_rows != 1) {
            $this->warnings["update"] = NO_UPDATE_CHANGE . "@User";
        }

        return true;
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

        if (!Validate::isId($this->userId)) {
            $valid = false;
            $this->warnings["id"] = NO_VALIDID . "@User";
        }

        if (strlen($this->accessToken) == 0) {
            $valid = false;
            $this->warnings["accessToken"] = NO_ACCESSTOKEN . "@User";
        }

        if (strlen($this->phoneId) == 0) {
            $valid = false;
            $this->warnings["phoneId"] = NO_PHONEID . "@User";
        }


        return $valid;
    }

    function checkExtraFields() {
        $valid = true;

        if (strlen($this->name) > 0 && !Validate::isPersonName($this->name, 120)) {
            $valid = false;
            $this->warnings["name"] = NO_VALID_NAME . "@User";
        }
        if (strlen($this->firstName) > 0 && !Validate::isPersonName($this->firstName, 120)) {
            $valid = false;
            $this->warnings["name"] = NO_VALID_NAME . "@User";
        }

        if (strlen($this->sex) > 0 && !Validate::isSex($this->sex)) {
            $valid = false;
            $this->warnings["sex"] = NO_VALID_SEX . "@User";
        }

        if (strlen($this->birthdate) > 0 && !Validate::isDate($this->birthdate) && false) {
            $valid = false;
            $this->warnings["birthdate"] = NO_VALID_DATETIME . "@User";
        }

        if (strlen($this->postalCode) > 0 && !Validate::isGermanZIP($this->postalCode)) {
            $valid = false;
            $this->warnings["postalCode"] = NO_VALID_POSTALCODE . "@User";
        }

        if (strlen($this->children) > 0 && !is_numeric($this->children)) {
            $valid = false;
            $this->warnings["children"] = NO_VALID_NUMBER . "@User";
        }

        if (mb_strlen($this->city) > 75) {
            $valid = false;
            $this->warnings["city"] = NO_VALID_CITY . "@User";
        }

        if (strlen($this->email) > 0 && !Validate::isEmail($this->email)) {
            $valid = false;
            $this->warnings["email"] = NO_VALID_EMAIL . "@User";
        }
        /*
          if (strlen($this->budget) > 0 && !is_numeric($this->budget)) {
          $valid = false;
          $this->warnings["budget"] = NO_VALID_NUMBER . "@User";
          } */

        return $valid;
    }

    /**
     * Speichert das Ansehen eines Inserats in der Datenbank
     * @param int $advertId Datenbank id des Inserats
     * @return int Status des Eintrags 
     */
    function addAdvertWachted($advertId) {
        //pruefe ob advertId bekannt
        $sql = "SELECT * FROM adverts WHERE id = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $advertId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows != 1) {
            return 0;
        }
        //sonst trage ein
        $sql2 = "INSERT INTO watched (userId, advertId) VALUES (?,?)";
        $stmt2 = Func::$db->prepare($sql2);
        $stmt2->bind_param('ii', $this->userId, $advertId);
        $stmt2->execute();
        $stmt2->store_result();

        return (Func::$db->affected_rows == 1) ? 1 : 2;
    }

    /**
     * Liefer ein Array mit allen betrachteten advertsIds
     * @return array Array mit advertIds
     */
    function getAdvertsWatched() {
        $advertIds = array();
        //pruefe ob advertId bekannt
        $sql = "SELECT advertId FROM watched WHERE userId = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $this->userId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($advertId);
        while ($stmt->fetch()) {
            $advertIds[$advertId] = $advertId;
        }
        return $advertIds;
    }

    /**
     * Speichert das Favorisieren eines Inserats in der Datenbank
     * @param int $advertId Datenbank id des Inserats
     * @return int Status des Eintrags 
     */
    function addAdvertFavourite($advertId) {
        //pruefe ob advertId bekannt
        $sql = "SELECT * FROM adverts WHERE id = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $advertId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows != 1) {
            return 0;
        }
        //sonst trage ein
        $sql2 = "INSERT INTO favourites (userId, advertId) VALUES (?,?)";
        $stmt2 = Func::$db->prepare($sql2);
        $stmt2->bind_param('ii', $this->userId, $advertId);
        $stmt2->execute();
        $stmt2->store_result();

        return (Func::$db->affected_rows == 1) ? 1 : 2;
    }

    /**
     * Entfernt den Favoriten aus der Datenbank
     * @param int $advertId Datenbank id des Inserats
     * @return int Status des Eintrags 
     */
    function removeAdvertFavourite($advertId) {
        //pruefe ob advertId bekannt
        $sql = "SELECT * FROM adverts WHERE id = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $advertId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows != 1) {
            return 0;
        }
        //sonst loesche
        $sql2 = "DELETE FROM favourites WHERE advertId = ? AND userId = ? LIMIT 1";
        $stmt2 = Func::$db->prepare($sql2);
        $stmt2->bind_param('ii', $advertId, $this->userId);
        $stmt2->execute();
        $stmt2->store_result();

        return (Func::$db->affected_rows == 1) ? 1 : 2;
    }

    /**
     * Liefer ein Array mit allen favorisierten advertsIds
     * @return array Array mit advertIds
     */
    function getAdvertsFavourite() {
        $advertIds = array();
        //pruefe ob advertId bekannt
        $sql = "SELECT advertId FROM favourites WHERE userId = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $this->userId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($advertId);
        while ($stmt->fetch()) {
            $advertIds[$advertId] = $advertId;
        }
        return $advertIds;
    }
    
    /**
     * Liefer ein Array mit allen Suchprofilen
     * @return array Array mit Suchprofilen Ids
     */
    function getProfiles() {
        $profilIds = array();
        //pruefe ob advertId bekannt
        $sql = "SELECT id FROM searchProfiles WHERE userId = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('i', $this->userId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($profilId);
        while ($stmt->fetch()) {
            $profilIds[$profilId] = $profilId;
        }
        return $profilIds;
    }
    
    /**
     * Liefert ein Profil Objekt als JSON
     * @param int $profilId Datenbank id des Inserats
     * @return string JSON
     */
    function getProfil($profilId) {
        $profil = Profil::newProfil($profilId);
        
        if(is_a($profil, "Profil")) {
            return $profil->toArray();
        } else {
            return null;
        }
    }

    function toArray() {
        $properties = get_object_vars($this);
        return $properties;
    }

    function getId() {
        return $this->userId;
    }

    function getPhoneId() {
        return $this->phoneId;
    }

    function getName() {
        return $this->name;
    }

    function getFirstName() {
        return $this->firstName;
    }

    function getSex() {
        return $this->sex;
    }

    function getJob() {
        return $this->job;
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

    
    function getAccessToken() {
        return $this->accessToken;
    }

    function getBirthdate() {
        return $this->birthdate;
    }

    function getPostalCode() {
        return $this->postalCode;
    }

    function getChildren() {
        return $this->children;
    }

    function getCity() {
        return $this->city;
    }

    function getEmail() {
        return $this->email;
    }

    function getCreated() {
        return $this->created;
    }

    function setId($id) {
        $this->userId = $id;
    }

    function setPhoneId($phoneId) {
        $this->phoneId = $phoneId;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    function setSex($sex) {
        $this->sex = $sex;
    }

    function setJob($job) {
        $this->job = $job;
    }

    function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    function setBirthdate($birthdate) {
        $this->birthdate = $birthdate;
    }

    function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
    }

    function setChildren($children) {
        $this->children = $children;
    }

    function setCity($city) {
        $this->city = $city;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getWarnings() {
        return $this->warnings;
    }

}
