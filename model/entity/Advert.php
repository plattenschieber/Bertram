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

    private $id;
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
        if (Validate::isId($this->id)) {
            $sql.= "id = ?  ";
        } else {
            $sql.= "externId = ?  ";
        }

        $stmt = Func::$db->prepare($sql);
        if (Validate::isId($this->id)) {
            $stmt->bind_param('i', $this->id);
        } else {
            $stmt->bind_param('i', $this->externId);
        }

        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows != 1) {
            return false;
        }

        $stmt->bind_result($this->id, $this->externId, $this->name, $this->description, $this->postalCode, $this->price, $this->balcony, $this->size, $this->type, $this->rooms, $this->imageUrl, $this->linkUrl, $this->lat, $this->lng);
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
        $stmt->bind_param("sssisisissddi", $this->name, $this->description, $this->postalCode, $this->price, $this->balcony, $this->size, $this->type, $this->rooms, $this->imageUrl, $this->linkUrl, $this->lat, $this->lng, $this->id);
        $stmt->execute();

        if (Func::$db->affected_rows != 1) {
            $this->warnings["update"] = NO_UPDATE_CHANGE . "@Advert";
        }

        return true;
    }

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
            $this->id = Func::$db->insert_id;
            return true;
        } else {
            $this->warnings['system'] = 'Fehler: #Insert-1@Advert';
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

    function checkExtraFields() {
        $valid = true;



        return $valid;
    }

    function toALSProduct($pId) {
        ob_start();
        ?>
        <product contentRefId="<?= $pId ?>" xmlns="http://als.medieninnovation.com/prod">
            <product_id><?= $this->id ?></product_id>
            <title><?= $this->name ?></title>
            <subTitle><?= $this->name ?></subTitle>
            <shortDescription><?= $this->name ?></shortDescription>
            <longDescription></longDescription>
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
            if (($fp = @fopen($this->imageUrl, 'r')) === true):
                @fclose($fp);
            
            ?>
            <image xmlns="http://als.medieninnovation.com/content">
                <url><?= $this->imageUrl ?></url>
                <width>118</width>
                <height>118</height>
                <minWidth>5</minWidth>
                <minHeight>5</minHeight>
                <alternateText></alternateText>
            </image>
             <?php endif; ?>
            <state>NEW</state>
            <availability>available</availability>
            <priceGross><?= $this->price ?></priceGross>
            <currency>EUR</currency>
            <taxes>0</taxes>

        </product>
        <?php
        return ob_get_clean();
    }

    function toArray() {
        $properties = get_object_vars($this);
        return $properties;
    }

    //Getter und Setter
    function getId() {
        return $this->id;
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
        $this->id = $id;
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

}
