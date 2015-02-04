<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */

class Validate {

    public static function isName($name, $length = 60) {
        $pattern = "/^[a-zA-Z\süÜäAöÖß\-\.0-9]{2," . $length . "}$/";
        return preg_match($pattern, $name);
    }

    public static function isPersonName($name, $length = 40) {
        $pattern = "/^[^!§\"$%&\?\(\)\[\]{}#*+~<>_:;]{2," . $length . "}$/";
        return preg_match($pattern, $name);
    }

    public static function isSex($input) {
        $pattern = "/^m|f$/";
        return preg_match($pattern, $input);
    }

    /**
     * Prueft ob Eingabe dem Format eines ID-Felds in der Datenbank entspricht
     * @param $id  zu pruefender string
     * @return boolean
     */
    public static function isId($id) {
        if (preg_match('/^[0-9]{1,10}$/', $id)) {
            return true;
        }
        return false;
    }

    /**
     * Prueft ob valide Email
     */
    public static function isEmail($email) {
        $pattern = "/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/";
        return preg_match($pattern, $email);
    }

    /**
     * Prueft ob valider Hash sha1
     */
    public static function isHash($hash) {
        $pattern = "/^.{40}$/";
        return preg_match($pattern, $hash);
    }

    /**
     * Prueft ob valide URL
     */
    public static function isURL($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function isStrasse($strasse, $length = 80) {
        $pattern = "/^[a-zA-Z\süÜäAöÖß\-\.]{2," . $length . "}$/";
        return preg_match($pattern, $strasse);
    }

    public static function isHausnr($input, $length = 5) {
        $pattern = "/^[0-9]{1," . $length . "}$/";
        return preg_match($pattern, $input);
    }

    public static function isGermanZIP($input) {
        $pattern = "/^[0-9]{5}$/";
        return preg_match($pattern, $input);
    }

    public static function isIntBoolean($input) {
        $pattern = "/^0|1$/";
        return preg_match($pattern, $input);
    }

    public static function isDatetime($input) {
        $pattern = "/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/";
        return preg_match($pattern, $input);
    }

    public static function isDate($input) {
        $pattern = "/^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/";
        return preg_match($pattern, $input);
    }

}
