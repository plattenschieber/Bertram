<?php

require_once(ROOT . '/core/Immocaster/Sdk.php');
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
        if (strlen($this->profil->getLat()) == 0 || strlen($this->profil->getLng()) == 0) {
            print $this->profil->getLat();
            return false;
        }
        /* //Suchstring erstellen
          $keywords = $profil->getFavoredCity();




          //get geoids for the search
          $aParameter = array('q' => $keywords);
          $regions = $this->api->getRegions($aParameter);
          $regionsArray = json_decode($regions[response]);
          print_r($regionsArray);
          print $keywords;

          //if no regions where found
          if (count($regionsArray->{'region.regions'}) == 0 || !is_array($regionsArray->{'region.regions'}[0]->region)) {
          return false;
          }

          //extract best geoId
          $geoIds = array();
          $limit = 1;
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
        print_r($estates);
        echo "check";
        if (!is_array($estates->{'resultlist.resultlist'}->resultlistEntries[0]->resultlistEntry)) {
            return;
        }

        foreach ($estates->{'resultlist.resultlist'}->resultlistEntries[0]->resultlistEntry as $estate) {
            $advert = Advert::newExAdvert($estate->realEstateId);
            if(!is_a($advert, "Advert")) {
                $advert = new Advert();
            }
            $item = new stdClass();
            $advert->setExternId($estate->realEstateId);
 
            $advert->setName($estate->{'resultlist.realEstate'}->title);
            $advert->setImageUrl($estate->{'resultlist.realEstate'}->titlePicture->urls[0]->url[0]->{'@href'});
            $advert->setPrice($estate->{'resultlist.realEstate'}->price->value);
            $advert->setSize($estate->{'resultlist.realEstate'}->livingSpace);
            
            
            $this->adverts[] = $item;
        }
    }

    /**
     * Gnereates Parameters for Search call
     * @param type $geoIds
     * @return array Parameter-Array 
     */
    private function getSearchParameter() {
        $gecoords = number_format($this->profil->getLat(), 6) . ";" . number_format($this->profil->getLng(), 6) . ";30"; //30km standart
        $aParameter = array('geocoordinates' => $gecoords, //;$geoIds[0]->geoCodeId
            'realestatetype' => ($this->profil->getBuy() == 1) ? 'apartmentbuy' : 'apartmentrent',
            'pagesize' => 10);

        if ($this->profil->getPrice() > 0) {
            $priceTo = intval($this->profil->getPrice() * 1.4);
            $priceFrom = intval($this->profil->getPrice() * 0.6);
            $aParameter['price'] = $priceFrom . '-' . $priceTo;
        }

        if ($this->profil->getSize() > 0) {
            $sizeTo = intval($this->profil->getSize() * 1.4);
            $sizeFrom = intval($this->profil->getSize() * 0.6);
            $aParameter['livingspace'] = $sizeFrom . '-' . $sizeTo;
        }

        if ($this->profil->getRooms() > 0) {
            $roomsTo = $this->profil->getRooms();
            $roomsFrom = $this->profil->getRooms();
            $aParameter['numberofrooms'] = $roomsFrom . '-' . $roomsTo;
        }

        if ($this->profil->getBalcony() == "Y") {
            $aParameter['equipment'] = 'balcony';
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
