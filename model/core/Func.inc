<?php

/* Stellt einige globale Funktionen zur Verfuegung
 * 
 * 
 * 
 *
 */

class Func {

    public static $db; //mysqli-Object

    /**
     * 	Gibt den aufgerufenen Pfad ohne GET-parameter wieder
     */

    public static function path() {
        $paths = explode('?', PERMALINK);
        return $paths[0];
    }

    /**
     * Verbindet mit Datenbank
     */
    public static function DBini() {
        Func::$db = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if (mysqli_connect_errno() != 0) {
            die('Keine Datenbankverbindung');
        }
        Func::$db->set_charset("utf8");
    }

    /**
     * Wandelt unschoene textzeichen in html zeichen um
     * @return String
     */
    public static function parseHTML($text) {
        $search = array(',,', '\\" ', '\n', ' - ', '<iframe', '<html', '</p>', '<object', '<embed', '</iframe>', '</html>', '</object>', '</embed>', '<div', '<span', '</div>', '</span>', '\\".');
        $replace = array('&bdquo;', '&ldquo; ', '<br />', ' &ndash; ', '', '', '', '', '', '', '', '', '', '', '', '', '', '&ldquo;');
        return str_replace($search, $replace, $text);
    }

    /**
     * Verhindert ungewollte htmltags in Textarea eingaben
     * @return String
     */
    public static function cleanHTML($text) {
        $search = array('/<\/?(div|iframe|html|object|embed|script|style|img|body|link|meta|table|input|td|tr|hr|head|noscript|form|h1|a)[^>]*>/i',
            '/"javascript:.*"/i',
            '/(on[^=]*|background)="?[^>]*"?/i',
            '/"/',
            '/\\\/'
        );
        $replace = array("", "", "", "&quot;", "");
        return preg_replace($search, $replace, $text);
    }

    /**
     * Ermittle die Geokoordinaten zu einer Adresse innerhalb deutschlands
     * @param type $search Suchstring der Adresse
     * @return \stdClass
     */
    public static function getLocation($search) {
        $location = new stdClass();
        $location->lat = null;
        $location->lng = null;
        $geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . urlencode($search . " Deutschland") . '&sensor=false');
        $output = json_decode($geocode);
        $besthit = $output->results[0];


        //|| ($besthit->types[0] != "locality" && $besthit->types[0] != "postal_code")
        if ($output->status != "OK") {
            return $location;
        }
        $location->lat = $besthit->geometry->location->lat;
        $location->lng = $besthit->geometry->location->lng;

        //print_r($besthit);

        return $location;
    }

    /**
     * Generiert zufaellige Zeichenkette mit 80 Zeichen
     * @return String 
     */
    public static function genKey($length = 80) {
        //buchstaben auswahl
        $zeichen = 'abcdefghkmnqlrstuvwxyzABDEFGLiIoO0HKMNQRT23456789';
        $laenge = strlen($zeichen) - 1;
        $code = '';

        //captcha code zeichenweise in bild einfuegen
        for ($i = 0; $i < $length; $i++) {
            $sign = $zeichen{mt_rand(0, $laenge)};
            $code .= $sign;
        }
        return $code;
    }

    /**
     * Entfernt alle html tags aus einem Text
     * @return String
     */
    public static function noHTML($text) {
        $search = array('/<\/?[^>]*>/',
            '/"/', '/\\\/');
        $replace = array("", "&quot;", "");
        return preg_replace($search, $replace, $text);
    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    public static function encrypt($pure_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, $pure_string, MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    public static function decrypt($encrypted_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }

    /**
     * Gibt eine aktuelle Datetime im Unix-Format zurueck
     * @return String
     */
    public static function currentDatetime() {
        return strftime("%Y-%m-%d %H:%M:%S");
    }

    /**
     * Wandelt ein deutsches Standarddatum (dd.mm.yyyy) in ein Unix-Datetime um
     * @param String $date
     * @return String
     */
    public static function convertStdDate($date) {
        return strftime("%Y-%m-%d %H:%M:%S", strtotime($date));
    }

    /**
     * Wandelt ein deutsches Datum mit ausgeschriebenen Monatsnamen in ein Unix-Datetime um
     * @param String $date
     * @return String
     */
    public static function convertGermanDate($date) {

        if (preg_match('/^[0-9]{2}\. .+ [0-9]{4}$/', $date)) {
            $search = array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");
            $rplc = array("01.", "02.", "03.", "04.", "05.", "06.", "07.", "08.", "09.", "10.", "11.", "12.");
            $return = str_replace($search, $rplc, $date);
            return self::convertStdDate(str_replace(" ", "", $return));
        } else {
            return Func::currentDatetime();
        }
    }
    
    //nicht gebraucht in diesem Framework
    public static function auth() {
        
    }

    /* maskiert email in ascii */

    public static function maskEmail($email) {
        $hiddenEmail = '';
        $length = strlen($email);

        for ($i = 0; $i < $length; $i++) {
            $hiddenEmail .= "&#" . ord($email[$i]) . ";";
        }

        return $hiddenEmail;
    }

    /**
     * Wandelt ein  Unix-Datetime in deutsches Datum um
     * @param String $date
     * @return String
     */
    public static function getGermanDate($date) {
        return strftime("%d.%m.%Y", strtotime($date));
    }

    /**
     * Wandelt ein  Unix-Datetime in deutsches Datum um
     * @param String $date
     * @return String
     */
    public static function getGermanDatetime($date) {
        return strftime("%d.%m.%Y %H:%M", strtotime($date));
    }

    /**
     * Wandelt ein  Unix-Datetime in deutsches Datum um
     * @param String $date
     * @return String
     */
    public static function getUnixDatetime($date) {
        return strftime("%Y-%m-%d %H:%M:%S", strtotime($date));
    }

    public static function rmDir($dirPath) {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }
    
    
    /**
     * Loescht eine Datei, checkt zuvor ob diese auch exisitiert
     * @param string $file Pfad der Datei
     * @return boolean
     */
    public static function saveDeleteFile($file) {
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

}
