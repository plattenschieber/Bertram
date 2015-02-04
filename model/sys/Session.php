<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */
require_once ROOT . '/model/entity/User.php';

/**
 * Description of Session
 *
 * @author Bertram
 */
class Session {

    private $user;

    public function auth() {
        if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === "POST") {
            $phoneId = filter_input(INPUT_POST, "phoneId", FILTER_SANITIZE_STRING);
            $accessToken = filter_input(INPUT_POST, "accessToken", FILTER_SANITIZE_STRING);
        } else {
            $phoneId = urldecode(filter_input(INPUT_GET, "phoneId", FILTER_SANITIZE_STRING));
            $accessToken = urldecode(filter_input(INPUT_GET, "accessToken", FILTER_SANITIZE_STRING));
        }


        $user = User::newPhoneUser($phoneId);
        if (is_a($user, "User") && $user->getAccessToken() === $accessToken && strlen($accessToken) != 0) {
            $this->user = $user;
            return true;
        }

        return false;
    }

    function getUser() {
        return $this->user;
    }

    public function kill() {
        session_destroy();
        session_regenerate_id();
        header("location: " . HOME . "/");
        die();
    }

}
