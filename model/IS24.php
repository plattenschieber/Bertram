<?php

require_once(ROOT . '/core/Immocaster/Sdk.php');
require_once ROOT . "/model/entity/Profil.php";
require_once ROOT . "/model/entity/Advert.php";

class IS24 {

    private $api; //is24 api Object
    private $adverts;
    private static $is24Key = 'MyFutureHomeEEKey';
    private static $is24Secret = '17kF0JTYXUfwt6sc';
    private $profil;

    /**
     * Constructor
     * @param type $res - response-Object
     */
    public function __construct() {
        $this->adverts = array();
        $this->api = Immocaster_Sdk::getInstance('is24', self::$is24Key, self::$is24Secret);
        $this->configAPI();
    }

    /**
     * Configure basic settings for the API
     */
    private function configAPI() {

        // $this->api->setDataStorage(array('mysql','DB-Host','DB-User','DB-Password','DB-Name'));

        /**
         * JSON verwenden
         */
        $this->api->setContentResultType('json');

        /**
         * Debug-Modus f�r Requests aktivieren
         * Zum deaktivieren: disableRequestDebug()
         */
        $this->api->enableRequestDebug();


        // $this->api->setStrictMode(true);


        $this->api->setRequestUrl('live');

        $this->api->authenticateWithoutDB(true);
    }

    /**
     * Initate the Search for results
     * @return type null
     */
    public function search($profil) {


        if (!is_a($profil, "Profil")) {
            return false;
        }

        $this->profil = $profil;
        if (strlen($profil->getLat()) == 0 || strlen($profil->getLng()) == 0) {
            return false;
        }
        /*
          //get geoids for the search
          $aParameter = array('q' => $keywords);
          $regions = $this->api->getRegions($aParameter);
          $regionsArray = json_decode($regions[response]);
          print_r($regions);
          //if no regions where found
          if (count($regionsArray->{'region.regions'}) == 0 || !is_array($regionsArray->{'region.regions'}[0]->region)) {
          return false;
          }


          //extract 5 best geoIds
          $geoIds = array();
          $limit = 5;
          $index = 0;

          foreach ($regionsArray->{'region.regions'}[0]->region as $region) {
          $geoIds[] = $region;
          $index++;
          if ($index >= $limit) {
          break;
          }
          }


          //if no geoids return
          if (count($geoIds) == 0) {
          return false;
          } */

        //get results based on geocodes
        $aParameter = $this->getSearchParameter();
        $result = $this->api->radiusSearch($aParameter);

        $estates = json_decode($result[response]);
        $this->buildEstates($estates);
    }

    /**
     * Get wanted information out of resultset
     * @param type $estates response from API
     * @return type null
     */
    private function buildEstates($estates) {

        $this->adverts = array();


        if (!is_array($estates->{'resultlist.resultlist'}->resultlistEntries[0]->resultlistEntry)) {
            return;
        }
        //save RSprofile user
        $sql = "INSERT INTO RS_searchProfiles_adverts (searchProfiles_id, adverts_id) VALUES (? ,?)";
        $stmt = Func::$db->prepare($sql);

        foreach ($estates->{'resultlist.resultlist'}->resultlistEntries[0]->resultlistEntry as $estate) {

            $advert = Advert::newExAdvert($estate->realEstateId);
            if (!is_a($advert, "Advert")) {
                $advert = new Advert();
            }
            $advert->setExternId($estate->realEstateId);
            $advert->setName($estate->{'resultlist.realEstate'}->title);

            $advert->setImageUrl($estate->{'resultlist.realEstate'}->titlePicture->urls[0]->url[1]->{'@href'});

            $postcode = $estate->{'resultlist.realEstate'}->address->postcode;
            $street = $estate->{'resultlist.realEstate'}->address->street;
            $city = $estate->{'resultlist.realEstate'}->address->city;
            $houseNumber = $estate->{'resultlist.realEstate'}->address->housenumber;
            $quarter = $estate->{'resultlist.realEstate'}->address->quarter;

            $advert->setDescription($postcode . " " . $city . " - " . $quarter . PHP_EOL . $street . " " . $houseNumber . PHP_EOL . PHP_EOL); ///todo

            $advert->setPostalCode($estate->{'resultlist.realEstate'}->address->postcode);
            $advert->setLinkUrl("http://www.immobilienscout24.de/expose/" . $estate->realEstateId);
            $advert->setPrice($estate->{'resultlist.realEstate'}->price->value);
            $advert->setSize($estate->{'resultlist.realEstate'}->livingSpace);
            $advert->setType(($estate->{'resultlist.realEstate'}->price->marketingType) ? "RENT" : "BUY");

            $lat = $estate->{'resultlist.realEstate'}->address->wgs84Coordinate->latitude;
            $lng = $estate->{'resultlist.realEstate'}->address->wgs84Coordinate->longitude;
            //falls keine geocords gefunden wurden
            if (strlen($lat) == 0 || strlen($lng) == 0) {
                $location = Func::getLocation($postcode . " " . $city . " " . $street);
                $lat = $location->lat;
                $lng = $location->lng;
            }
            //falls keine geocords gefunden  redo nur mit stadt
            if (strlen($lat) == 0 || strlen($lng) == 0) {
                $location = Func::getLocation($city);
                $lat = $location->lat;
                $lng = $location->lng;
            }


            $advert->setLat($lat);
            $advert->setLng($lng);
            $advert->setBalcony(($estate->{'resultlist.realEstate'}->balcony) ? 1 : 0);
            $advert->setRooms($estate->{'resultlist.realEstate'}->numberOfRooms);

            if ($advert->saveToDB()) {
                $stmt->bind_param("ii", $this->profil->getId(), $advert->getId());
                $stmt->execute();
            }

            $this->adverts[] = $advert;
        }
    }

    /**
     * Gnereates Parameters for Search call
     * @param type $geoIds
     * @return array Parameter-Array 
     */
    private function getSearchParameter() {
        $coords = number_format($this->profil->getLat(), 6) . ";" . number_format($this->profil->getLng(), 6) . ";30"; //30km default umkreis
        $aParameter = array('geocoordinates' => $coords, //$geoIds[0]->geoCodeId, //nur die erste geoid wird genutzt
            'realestatetype' => ($this->profil->getBuy() == 1) ? 'apartmentbuy' : 'apartmentrent',
            'pagesize' => 200);

        if (strlen($this->profil->getPrice()) > 0) {
            $priceTo = intval($this->profil->getPrice() * 1.2);
            $priceFrom = intval($this->profil->getPrice() * 0.4);
            $aParameter['price'] = $priceFrom . '-' . $priceTo;
        }

        if (strlen($this->profil->getSize()) > 0) {
            $sizeTo = intval($this->profil->getSize() * 1.3);
            $sizeFrom = intval($this->profil->getSize() * 0.7);
            $aParameter['livingspace'] = $sizeFrom . '-' . $sizeTo;
        }

        if (strlen($this->profil->getRooms()) > 0) {
            $roomsTo = $this->profil->getRooms() + 1;
            $roomsFrom = $this->profil->getRooms() - 1;
            if ($roomsFrom == 0) {
                $roomsFrom = 1;
            }
            $aParameter['numberofrooms'] = $roomsFrom . '-' . $roomsTo;
        }

        return $aParameter;
    }

    function getAdverts() {
        return $this->adverts;
    }

    function setAdverts($adverts) {
        $this->adverts = $adverts;
    }

}
