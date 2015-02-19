<?php

/**
 * Description of ALSConnector
 *
 * @author Bertram
 */
class ALSConnector {

    //API Key der ALS Schnittstelle
    private static $API_KEY = "fBM7hXn5C6XEXHDYvxK2XCqm34eU4cAD";
    //URL um den Status eines JOBS zu pruefen
    private static $STATUS_URL = "http://als.dev.medieninnovation.com:4003/jobStatus/";
    //URL um einen JOB zu uebermitteln
    private static $POST_URL = "http://als.dev.medieninnovation.com:4003/postJob/";
    //JobId des aktuellen Jobs der an ALS uebermittelt wurde
    private $jobId;
    //XML string des jobs
    private $xml;
    private $height;
    private $width;

    /**
     * Verarbeitet einen Aufruf an ALS und gibt nach Erfolg die entsprechende Response zurueck
     * @param type $adverts Array an Adverts Objekten
     * @param type $file Eingabe XML ist Pfad zu XML Datei?
     */
    function handleJob($adverts, $file = false) {

        $header = $this->buildXMLHaed();
        $footer = $this->buildXMLFooter($adverts);
        $body = '';
        $index = 1;
        $keyPrefix = "p";
        foreach ($adverts as $advert) {
            if (!is_a($advert, "Advert")) {
                continue;
            }
            $body .= $advert->toALSProduct($keyPrefix . $index);
            if ($index == MAX_ADVERTS) {
                break;
            }
            $index++;
        }

        $this->xml = $header . $body . $footer;
        //print_r($this->xml);
        //init curlObj
        $ch = curl_init(self::$POST_URL . "?apiKey=" . self::$API_KEY);

        $post = $this->genPost($this->xml, $file);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $results = curl_exec($ch);

        //close connection
        curl_close($ch);
        return $results;
    }

    /**
     * Generiert POST String zur ALS Abfrage
     * @param type $xml Eingabe XML JobCollection
     * @param type $file Eingabe XML ist Pfad zu XML Datei?
     * @return string
     */
    private function genPost($xml, $file = false) {


        return http_build_query(array('jobFile' => $xml));
    }

    private function buildXMLHaed() {
        return '<?xml version="1.0" encoding="UTF-8"?>
                <jobCollection xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://als.medieninnovation.com/job" xmlns:prod="http://als.medieninnovation.com/prod"
                        xmlns:content="http://als.medieninnovation.com/content"
                        xsi:schemaLocation="http://als.medieninnovation.com/job http://als.dev.medieninnovation.com/schema/JobCollection.xsd http://als.medieninnovation.com/prod http://als.dev.medieninnovation.com/schema/ProductCollection.xsd http://als.medieninnovation.com/content http://als.dev.medieninnovation.com/schema/ContentCollection.xsd">
                    <contentCollection xmlns="http://als.medieninnovation.com/content">
		';
    }

    private function buildXMLFooter($adverts) {

        $index = 1;
        $keyPrefix = "p";
        $count = 0;
        foreach ($adverts as $advert) {
            if (!is_a($advert, "Advert")) {
                continue;
            }
            $count++;
        }
        ob_start();
        ?>
        </contentCollection>
        <job>
            <pageCollection>

                <multiPage maxPages="10">
                    <contentToPageCollection>
                        <?php
                        foreach ($adverts as $advert) {
                            if (!is_a($advert, "Advert")) {
                                continue;
                            }

                            echo '<contentToPage>
                            <contentRef>' . $keyPrefix . $index . '</contentRef>
                            <weight>' . number_format((1 / (1 + $advert->getPriority())), 4) . '</weight>
                        </contentToPage>';

                            if ($index == MAX_ADVERTS) {
                                break;
                            }
                            $index++;
                        }
                        ?>
                    </contentToPageCollection>
                    <pageDesign>
                        <css>
                            page{
                            height: <?= $this->height ?>px;
                            width:<?= $this->width ?>px;

                            }</css>
                    </pageDesign>
                </multiPage>
                <pageDesign>


                </pageDesign>
            </pageCollection>
            <layoutGeneratorTable />
            <outputCollection>

                <outputJSON outputRefId="outputJson" directOutput="true" />
            </outputCollection>
            <!-- <senderCollection> -->
            <!-- <senderPost> -->
            <!-- <outputRef>outputWebserver</outputRef> -->
            <!-- <url>http://client.als.dev.medieninnovation.com/post.php?action=save</url> -->
            <!-- <fieldnameJobId>id</fieldnameJobId> -->
            <!-- <fieldnameFile>file</fieldnameFile> -->
            <!-- <fieldnameError>error</fieldnameError> -->
            <!-- </senderPost> -->
            <!-- <senderMail> -->
            <!-- <outputRef>outputWebserver</outputRef> -->
            <!-- <to>_FILLIN_</to> -->
            <!-- <from>als@medieninnovation.com</from> -->
            <!-- <subject>New ALS output document</subject> -->
            <!-- <attachment>false</attachment> -->
            <!-- </senderMail> -->
            <!-- </senderCollection> -->
            <configuration>
                <apiKey>fBM7hXn5C6XEXHDYvxK2XCqm34eU4cAD</apiKey>
                <language>de-DE</language>
                <minLayouts>10</minLayouts>
                <maxRenderingTime>30</maxRenderingTime>
            </configuration>
        </job>
        </jobCollection>
        <?php
        return ob_get_clean();
    }

    function getHeight() {
        return $this->height;
    }

    function getWidth() {
        return $this->width;
    }

    function setHeight($height) {
        $this->height = $height;
    }

    function setWidth($width) {
        $this->width = $width;
    }

}
