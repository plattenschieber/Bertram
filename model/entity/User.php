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

    private $id;
    private $phoneId;
    private $name;
    private $firstName;
    private $age;
    private $sex;
    private $job;
    private $accessToken;
    private $birthdate;
    private $postalCode;
    private $children;
    private $city;
    private $email;
    private $budget;
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
                . "users.age, "
                . "users.sex, "
                . "users.job, "
                . "users.accessToken, "
                . "users.birthdate, "
                . "users.postalCode, "
                . "users.city, "
                . "users.email, "
                . "users.budget,"
                . "users.created, "
                . "users.children "
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
            $stmt->bind_param('i', $this->id);
        }

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows != 1) {
            return false;
        }

        $stmt->bind_result($this->id, $this->phoneId, $this->name, $this->firstName, $this->age, $this->sex, $this->job, $this->accessToken, $this->birthdate, $this->postalCode, $this->city, $this->email, $this->budget, $this->created, $this->children);
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
                . "email = ?, "
                . "budget = ? "
                . "WHERE id = ? "
                . "LIMIT 1";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("sssssiissii", $this->name, $this->firstName, $this->sex, $this->job, $this->birthdate, $this->postalCode, $this->children, $this->city, $this->email, $this->budget, $this->id);
        $stmt->execute();

        if (Func::$db->affected_rows != 1) {
            $this->warnings["update"] = NO_UPDATE_CHANGE . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
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

        if (!Validate::isId($this->id)) {
            $valid = false;
            $this->warnings["id"] = NO_VALIDID . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->accessToken) == 0) {
            $valid = false;
            $this->warnings["accessToken"] = NO_ACCESSTOKEN . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->phoneId) == 0) {
            $valid = false;
            $this->warnings["phoneId"] = NO_PHONEID . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }


        return $valid;
    }

    function checkExtraFields() {
        $valid = true;

        if (strlen($this->name) > 0 && !Validate::isPersonName($this->name, 120)) {
            $valid = false;
            $this->warnings["name"] = NO_VALID_NAME . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }
        if (strlen($this->firstName) > 0 && !Validate::isPersonName($this->firstName, 120)) {
            $valid = false;
            $this->warnings["name"] = NO_VALID_NAME . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->sex) > 0 && !Validate::isSex($this->sex)) {
            $valid = false;
            $this->warnings["sex"] = NO_VALID_SEX . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->birthdate) > 0 && !Validate::isDate($this->birthdate)) {
            $valid = false;
            $this->warnings["birthdate"] = NO_VALID_DATE . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->postalCode) > 0 && !Validate::isGermanZIP($this->postalCode)) {
            $valid = false;
            $this->warnings["postalCode"] = NO_VALID_POSTALCODE . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->children) > 0 && !is_numeric($this->children)) {
            $valid = false;
            $this->warnings["children"] = NO_VALID_NUMBER . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (mb_strlen($this->city) > 75) {
            $valid = false;
            $this->warnings["city"] = NO_VALID_CITY . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->email) > 0 && !Validate::isEmail($this->email)) {
            $valid = false;
            $this->warnings["email"] = NO_VALID_EMAIL . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
        }

        if (strlen($this->budget) > 0 && !is_numeric($this->budget)) {
            $valid = false;
            $this->warnings["budget"] = NO_VALID_NUMBER . "@" . filter_input(INPUT_SERVER, 'PHP_SELF');
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

    function getPhoneId() {
        return $this->phoneId;
    }

    function getName() {
        return $this->name;
    }

    function getFirstName() {
        return $this->firstName;
    }

    function getAge() {
        return $this->age;
    }

    function getSex() {
        return $this->sex;
    }

    function getJob() {
        return $this->job;
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

    function getBudget() {
        return $this->budget;
    }

    function getCreated() {
        return $this->created;
    }

    function setId($id) {
        $this->id = $id;
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

    function setAge($age) {
        $this->age = $age;
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

    function setBudget($budget) {
        $this->budget = $budget;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getWarnings() {
        return $this->warnings;
    }

}
