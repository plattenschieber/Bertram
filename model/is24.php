<?php
require_once(ROOT.'/core/Immocaster/Sdk.php');

class IS24 {

    private $api; //is24 api Object
    private $adverts;
    
    private static $is24Key = 'MyFutureHomeEEKey';
    private static $is24Secret = '17kF0JTYXUfwt6sc';

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
    public function search() {
        $keywords = "Berlin";//todo suchparameter erstellen

        if (strlen($keywords) < 3) {
            return false;
        }
        
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
        }    

        //get results based on geocodes
        $aParameter = $this->getSearchParameter($geoIds);
        $result = $this->api->regionSearch($aParameter);

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
            $item = new stdClass();

            $item->id = $estate->realEstateId;
            $item->title = $estate->{'resultlist.realEstate'}->title;
            $item->address = $estate->{'resultlist.realEstate'}->address;
            $item->price = $estate->{'resultlist.realEstate'}->price->value;
            $item->livingSpace = $estate->{'resultlist.realEstate'}->livingSpace;
            $item->picture = $estate->{'resultlist.realEstate'}->titlePicture->urls[0]->url[0]->{'@href'};
             $this->adverts[] = $item;
        }


        
    }

    /**
     * Gnereates Parameters for Search call
     * @param type $geoIds
     * @return array Parameter-Array 
     */
    private function getSearchParameter($geoIds) {
        $aParameter = array('geocodes' => $geoIds[0]->geoCodeId, //nur die erste geoid wird genutzt
            'realestatetype' => 'apartmentrent',
            'pagesize' => 10);

        if (isset($_POST['range-to:preis'])) {
            $priceTo = $_POST['range-to:preis'];
        }

        if (isset($_POST['range-from:preis'])) {
            $priceFrom = $_POST['range-from:preis'];
        }

        if (strlen($priceTo . $priceFrom) > 0) {
            $aParameter['price'] = $priceFrom . '-' . $priceTo;
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
