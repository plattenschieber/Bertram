<?php

/*
 * Autor Bertram Buchardt
 * Alle Rechte vorbehalten
 * 
 */


/**
 * Description of Session
 *
 * @author Bertram
 */
class Session {

    
    private $logged;
   

    public function isLogged() {
        return $this->logged;
    }

    public function login($email, $passwort, $perm = false) {
        //Pruefe ob Eingaben korrekt
        $sql = "SELECT id FROM user WHERE email = ? AND passwort = ?";
        $stmt = Func::$db->prepare($sql);
        $stmt->bind_param('ss', strtolower($email), sha1($passwort . HASH_KIT));
        $stmt->execute();
        $stmt->store_result();


        //Falls valider Login
        if ($stmt->num_rows == 1) {

            $stmt->bind_result($id);

            $stmt->fetch();

            //save login as cookie for the next 3 days
            if ($perm) {
                $token = Func::genKey(120);
                $token_check = sha1($_SERVER[HTTP_USER_AGENT]);

                $time = time();
                $timeC = mktime(03, 0, 0, date("m", $time), date("d", $time) + 3, date("Y", $time));
                $timeT = strftime('%Y-%m-%d %H:%M:%S', $timeC);

                $sql = "UPDATE user SET session_id = ?, token = ?, token_check = ?, token_expire = '" . $timeT . "' WHERE id = ? LIMIT 1";
                $stmt2 = Func::$db->prepare($sql);
                $stmt2->bind_param('sssi', session_id(), $token, $token_check, $id);
                $stmt2->execute();

                setcookie("TOKEN", $token, $timeC, "/");
            } else {
                $sql = "UPDATE user SET session_id = ? WHERE id = ? LIMIT 1";
                $stmt2 = Func::$db->prepare($sql);
                $stmt2->bind_param('si', session_id(), $id);
                $stmt2->execute();
            }

            $this->user = User::newUser($id);

            if (isset($this->user)) {
                $this->logged = true;
            }

            return true;
        } else {
            return false;
        }
    }

    public function auth() {
        return true;
        if ($this->isLogged()) {
            if (!$this->user->auth()) {
                $this->kill();

                return false;
            }
            return true;
        }
    }

   

    public function kill() {
        $this->logged = false;
       

        session_destroy();
        session_regenerate_id();
        header("location: " . HOME . "/");
        die();
    }

}
