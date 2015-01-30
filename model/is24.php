<?php
require_once('Immocaster/Sdk.php');

class IS24 {

    private $api; //is24 api Object
    private $res; //response
    private static $is24Key = 'Sky-Hype-PropertiesKey';
    private static $is24Secret = '6dHH5v3GYebqT9TR';

    /**
     * Constructor
     * @param type $res - response-Object
     */
    public function __construct($res) {
        $this->res = $res;
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
        $keywords = $_POST[keywords];

        if (strlen($keywords) < 3) {
            return;
        }

        //get geoids for the search
        $aParameter = array('q' => $keywords);
        $regions = $this->api->getRegions($aParameter);
        $regionsArray = json_decode($regions[response]);

        //if no regions where found
        if (count($regionsArray->{'region.regions'}) == 0 || !is_array($regionsArray->{'region.regions'}[0]->region)) {
            return;
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
            return;
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
        $estateList = array();

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
            $estateList[] = $item;
        }


        //$this->res->estates = $estateList;
        $this->genHTML($estateList);
    }

    /**
     * Gnereates Parameters for Search call
     * @param type $geoIds
     * @return array Parameter-Array 
     */
    private function getSearchParameter($geoIds) {
        $aParameter = array('geocodes' => $geoIds[0]->geoCodeId,
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

    /**
     * Generates HTMl response
     * @param type $estateList Array of estate-Objects
     */
    private function genHTML($estateList) {
        $htmlList = array();
        foreach ($estateList as $estate) {
            $htmlList[] = $this->getItemHTML($estate);
        }

        $this->res->html = $htmlList;
    }

    /**
     * Generate HTML for single Estate-Object
     * @param Object Estate-Object
     * @return string HTML
     */
    private function getItemHTML($estate) {
        ob_start();
        ?>
        <div class="entry">
            <div class="row">
                <div class="col-xs-4">

                    <a href="http://www.immobilienscout24.de/expose/<?= $estate->id ?>">

                        <img src="<?= $estate->picture ?>" alt="Kein Bild" style="margin-top: 20px; margin-bottom: 20px;">
                    </a>

                </div>
                <div class="col-xs-8">
                    <a href="http://www.immobilienscout24.de/expose/<?= $estate->id ?>"><h2 style="margin-bottom:8px;"><?= $estate->title ?></h2></a>
                    <div class="row">
                        <div class="col-xs-12">
                            <p style="line-height:14px;"><?= $estate->address->street ?> <br>
                                <?= $estate->address->city ?>, <?= $estate->address->quarter ?></p>
                            <p><?= number_format($estate->price, 2, ',', '.') ?> € | <?= number_format($estate->livingSpace, 0) ?> m²</p>

                        </div>
                    </div>

                </div>
                <div class="extern-hint col-xs-12"><small class="text-primary">Eine Anzeige von ImmobillienScout24.</small></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}
