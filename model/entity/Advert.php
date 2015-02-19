<?php
/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */

/**
 * Description of Advert
 *
 * @author Bertram
 */
class Advert {

    private $advertId;
    private $externId;
    private $name;
    private $description;
    private $postalCode;
    private $price;
    private $balcony;
    private $size;
    private $type;
    private $rooms;
    private $imageUrl;
    private $linkUrl;
    private $lat;
    private $lng;
    private $warnings;
    private $priority;

    /**
     * Konstruktor
     */
    function __construct() {
        $this->warnings = array();
    }

    /**
     * Static Konstruktur: zum erzeugen einer Anzeige anhand der Id
     * @param int $id
     * @return \User
     */
    public static Function newAdvert($id) {
        $self = new Advert();
        $self->setId($id);
        if ($self->loadFromDB()) {
            return $self;
        }
        return null;
    }

    /**
     * Static Konstruktur: zum erzeugen einer Anzeige anhand einer externen Id
     * @param int $id
     * @return \User
     */
    public static Function newExAdvert($id) {
        $self = new Advert();
        $self->setExternId($id);
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
        $sql = "SELECT adverts.id, "
                . "adverts.externId, "
                . "adverts.name, "
                . "adverts.description, "
                . "adverts.postalCode, "
                . "adverts.price, "
                . "adverts.balcony, "
                . "adverts.size, "
                . "adverts.type, "
                . "adverts.rooms, "
                . "adverts.imageUrl, "
                . "adverts.linkUrl, "
                . "adverts.lat, "
                . "adverts.lng "
                . "FROM adverts WHERE ";
        if (Validate::isId($this->advertId)) {
            $sql.= "id = ?  ";
        } else {
            $sql.= "externId = ?  ";
        }

        $stmt = Func::$db->prepare($sql);
        if (Validate::isId($this->advertId)) {
            $stmt->bind_param('i', $this->advertId);
        } else {
            $stmt->bind_param('i', $this->externId);
        }

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 1) {
            return false;
        }

        $stmt->bind_result($this->advertId, $this->externId, $this->name, $this->description, $this->postalCode, $this->price, $this->balcony, $this->size, $this->type, $this->rooms, $this->imageUrl, $this->linkUrl, $this->lat, $this->lng);
        $stmt->fetch();

        return true;
    }

    /**
     * Speichert das Objekt in die Datenbank insert oder update.
     * @return boolean
     */
    function saveToDB() {
        if (isset($this->advertId) && Validate::isId($this->advertId)) {
            return $this->updateDB();
        } else {
            return $this->insertDB();
        }
    }

    function updateDB() {
        if (!$this->isValid()) {
            return false;
        }
        $sql = "UPDATE adverts SET "
                . "name = ?, "
                . "description = ?, "
                . "postalCode = ?, "
                . "price = ?, "
                . "balcony = ?, "
                . "size = ?, "
                . "type = ?, "
                . "rooms = ?, "
                . "imageUrl = ?, "
                . "linkUrl = ?, "
                . "lat = ?, "
                . "lng = ? "
                . "WHERE id = ? "
                . "LIMIT 1";

        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("sssisisissddi", $this->name, $this->description, $this->postalCode, $this->price, $this->balcony, $this->size, $this->type, $this->rooms, $this->imageUrl, $this->linkUrl, $this->lat, $this->lng, $this->advertId);
        $stmt->execute();

        if (Func::$db->affected_rows != 1) {
            $this->warnings["update"] = NO_UPDATE_CHANGE . "@Advert";
        }

        return true;
    }

    /**
     * Verfuegt das Objekt noch ueber keine ID wird es per INSERT 
     * in der DB gespeichert
     * @return boolean
     */
    function insertDB() {
        if (!$this->isValid()) {
            return false;
        }
        $sql = "INSERT INTO adverts ( "
                . "externId, "
                . "name, "
                . "description, "
                . "postalCode, "
                . "price, "
                . "balcony, "
                . "size, "
                . "type, "
                . "rooms, "
                . "imageUrl, "
                . "linkUrl, "
                . "lat, "
                . "lng ) "
                . "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) ";

        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param("isssisisissdd", $this->externId, $this->name, $this->description, $this->postalCode, $this->price, $this->balcony, $this->size, $this->type, $this->rooms, $this->imageUrl, $this->linkUrl, $this->lat, $this->lng);
        $stmt->execute();

        if (Func::$db->affected_rows == 1) {
            $this->advertId = Func::$db->insert_id;



            return true;
        } else {
            $this->warnings['system'] = 'Fehler: #Insert-1@Advert';
            return false;
        }
    }

    /**
     * Prueft ob die Felder des Objekts valide sind
     * @return boolean
     */
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

    /**
     * Prueft Pflichtfelder
     * @return boolean
     */
    function checkBasicFields() {
        $valid = true;

        if (isset($this->advertId) && !Validate::isId($this->advertId)) {
            $valid = false;
            $this->warnings["id"] = NO_VALIDID . "@Advert";
        }

        if (!Validate::isId($this->externId)) {
            $valid = false;
            $this->warnings["externId"] = NO_VALIDID . "@Advert";
        }

        if (strlen($this->name) == 0) {
            $valid = false;
            $this->warnings["name"] = NO_ADVERT_NAME . "@Advert";
        }


        return $valid;
    }

    /**
     * Prueft optinale Felder
     * @return boolean
     */
    function checkExtraFields() {
        $valid = true;

        //Prototyping no checks here


        return $valid;
    }

    /**
     * Convertier das Objekt zu einem XML-Objekt nach http://als.medieninnovation.com/prod
     * @return string
     */
    function toALSProduct($pId) {
        ob_start();
        ?>
        <product contentRefId="<?= $pId ?>" xmlns="http://als.medieninnovation.com/prod">
            <product_id><?= $this->advertId ?></product_id>
            <title><?= urlencode($this->name) ?></title>
            <subTitle><?= urlencode($this->name) ?></subTitle>
            <shortDescription><?= urlencode($this->description) ?></shortDescription>

            <customProperty>
                <name>Balkon</name>
                <value><?= ($this->balcony == "Y") ? 'ja' : 'nein' ?></value>
                <datatype>string</datatype>
            </customProperty>
            <customProperty>
                <name>Typ</name>
                <value><?= ($this->type == "RENT") ? 'zur Miete' : 'zum Kauf' ?></value>
                <datatype>string</datatype>
            </customProperty>
            <customProperty>
                <name>Größe</name>
                <value><?= $this->size ?> m²</value>
                <datatype>string</datatype>
            </customProperty>
            <customProperty>
                <name>Räume</name>
                <value><?= $this->rooms ?></value>
                <datatype>string</datatype>
            </customProperty>
            <link><?= $this->linkUrl ?></link>
            <?php
            if (strlen($this->imageUrl) > 0 && @file_get_contents($this->imageUrl, 0, null, 0, 1) !== false):
                ?>
                <image xmlns="http://als.medieninnovation.com/content">
                    <url><?= $this->imageUrl ?></url>
                    <width>210</width>
                    <height>210</height>
                    <minWidth>5</minWidth>
                    <minHeight>5</minHeight>
                    <alternateText></alternateText>
                </image>
        <?php endif; ?>
            <state>NEW</state>
            <availability>available</availability>
            <priceGross><?= intval($this->price) ?></priceGross>
            <currency>EUR</currency>
            <taxes>0</taxes>

        </product>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Konvertiert die Felder des Objekts als Array-Darstellung
     * @return array
     */
    function toArray() {
        $properties = get_object_vars($this);
        return $properties;
    }

    //Getter und Setter
    function getId() {
        return $this->advertId;
    }

    function getExternId() {
        return $this->externId;
    }

    function getDescription() {
        return $this->description;
    }

    function getPostalCode() {
        return $this->postalCode;
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

    function getType() {
        return $this->type;
    }

    function getRooms() {
        return $this->rooms;
    }

    function getImageUrl() {
        return $this->imageUrl;
    }

    function getLinkUrl() {
        return $this->linkUrl;
    }

    function getLat() {
        return $this->lat;
    }

    function getLng() {
        return $this->lng;
    }

    function getWarnings() {
        return $this->warnings;
    }

    function setId($id) {
        $this->advertId = $id;
    }

    function setExternId($externId) {
        $this->externId = $externId;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setPostalCode($postalCode) {
        $this->postalCode = $postalCode;
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

    function setType($type) {
        $this->type = $type;
    }

    function setRooms($rooms) {
        $this->rooms = $rooms;
    }

    function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
    }

    function setLinkUrl($linkUrl) {
        $this->linkUrl = $linkUrl;
    }

    function setLat($lat) {
        $this->lat = $lat;
    }

    function setLng($lng) {
        $this->lng = $lng;
    }

    function getName() {
        return $this->name;
    }

    function setName($name) {
        $this->name = $name;
    }
    
    public function getPriority() {
        return $this->priority;
    }

    public function setPriority($priority) {
        $this->priority = $priority;
    }



}
