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

    /**
     * Verarbeitet einen Aufruf an ALS und gibt nach Erfolg die entsprechende Response zurueck
     * @param type $xml Eingabe XML JobCollection
     * @param type $file Eingabe XML ist Pfad zu XML Datei?
     */
    function handleJob($xml, $file = false) {
        //init curlObj
        $ch = curl_init(self::$POST_URL . "?apiKey=" . self::$API_KEY);

        $post = $this->genPost($xml, $file);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $res = curl_exec($ch);

        //close connection
        curl_close($ch);
        return $res;
    }

    /**
     * Generiert POST String zur ALS Abfrage
     * @param type $xml Eingabe XML JobCollection
     * @param type $file Eingabe XML ist Pfad zu XML Datei?
     * @return string
     */
    private function genPost($xml, $file = false) {
        //print_r($xml);

        return http_build_query(array('jobFile' => $xml));
    }

}
