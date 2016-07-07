<?php

require_once "./config.php";

/**
 * Description of fusionapi
 *
 * @author LARIVIERE Julien
 */
class fusionapi {

    public static function connecRPCClient() {
        try {
            /* We create the connection object */
            $client = new jsonRPCClient(host, http_options, ssl_options);
            /* Then we need to login. Here we log in the default LDAP */
        } catch (jsonRPCClient_RequestErrorException $e) {
            die($e->getMessage());
        } catch (jsonRPCClient_NetworkErrorException $e) {
            die($e->getMessage());
        }

        return $client;
    }

    public static function addUser($name, $familyname, $mail) {

        $client = fusionapi::connecRPCClient();

        $session_id = $client->login(NULL, login, password);

        // convertion de l'adresse email en nom d'utilisateur
        $email = explode('@', $mail);
        // supprimer les . du nom d'utilisateur => incompatible avec le champ 'uid'
        $username = str_replace('.', '', $email[0]);

        // creation de l'utilisateur
        $password = fusionapi::generate_password(pwd_length);
        $result = $client->formpost($session_id, 'user', NULL, 'user', array(
            'uid' => "$username",
            'givenName' => "$name",
            'sn' => "$familyname",
            'userPassword_password' => $password,
            'userPassword_password2' => $password,
            'userPassword_pwstorage' => 'ssha',
        ));

        echo $username . ' - ' . $password;
        // récupération du DN de l'utilisateur créé
        $attr = array(
            'uid' => 1,
        );
        $result = $userDN = $client->ls($session_id, 'user', $attr, NULL, "uid=$username");

        $userDN = array_keys($userDN);
        // ajout de l'adresse email à l'utilisateur
        $result = $client->formpost($session_id, 'user', $userDN[0], 'mailAccount', array(
            'mail' => "$mail",
        ));

        // ajout du compte UNIX à l'utilisateur
        $result = $client->formpost($session_id, 'user', $userDN[0], 'posixAccount', array(
            'homeDirectory' => "/home/$username",
            'loginShell' => '/bin/ash',
        ));

        // si le domaine Samba est indiqué
        $smbDomain = sambadomain;
        if (isset($smbDomain) && ($smbDomain != '')) {
            // ajout de l'onglet Samba à l'utilisateur
            $result = $client->formpost($session_id, 'user', $userDN[0], 'sambaAccount', array(
            ));

            // récupération du CN du group de l'utilisateur
            $attrGroup = array(
                'cn' => 1,
            );
            $groupDN = $client->ls($session_id, 'group', $attrGroup, NULL, "cn=$username");
            $groupDN = array_keys($groupDN);
            // ajout de l'onglet Samba au groupe de l'utilisateur
            $result = $client->formpost($session_id, 'group', $groupDN[0], 'sambaGroup', array(
                'sambaDomainName' => sambadomain,
            ));
        }

        if (isset($result['errors'])) {
            foreach ($result['errors'] as $error) {
                print "Error: $error\n";
            }
        }
    }

    public static function modifyUser($name, $familyname, $mail) {

        /* We create the connection object */
        $client = fusionapi::connecRPCClient();
        /* Then we need to login. Here we log in the default LDAP */
        $session_id = $client->login(NULL, login, password);

        // Récupération du "DN" de l'utilisateur
        $attr = array(
            'mail' => 1,
            'uid' => 1,
        );
        $userDN = $client->ls($session_id, 'user', $attr, NULL, "mail=$mail");
        $userDN = array_keys($userDN);
        //
        $result = $client->formpost($session_id, 'user', $userDN[0], 'user', array(
            'givenName' => "$name",
            'sn' => "$familyname",
        ));

        if (isset($result['errors'])) {
            foreach ($result['errors'] as $error) {
                print "Error: $error\n";
            }
        }
    }

    /*
     * Génération du hash du mot de passe
     */

    private static function generate_hash($pwd) {
        if (function_exists("sha1")) {
            $salt = substr(pack("h*", md5(mt_rand())), 0, 8);
            $salt = substr(pack("H*", sha1($salt . $pwd)), 0, 4);
            $pwd = "{SSHA}" . base64_encode(pack("H*", sha1($pwd . $salt)) . $salt);
            return $pwd;
        } elseif (function_exists("mhash")) {
            $salt = mhash_keygen_s2k(MHASH_SHA1, $pwd, substr(pack("h*", md5(mt_rand())), 0, 8), 4);
            $pwd = "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pwd . $salt) . $salt);
        } else {
            msg_dialog::display(_("Configuration error"), msgPool::missingext("mhash"), ERROR_DIALOG);
            return FALSE;
        }
        return $pwd;
    }

    private static function generate_password($length, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
        // Length of character list
        $chars_length = (strlen($chars) - 1);
        // Start our string
        $string = $chars{rand(0, $chars_length)};
        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string)) {
            // Grab a random character from our list
            $r = $chars{rand(0, $chars_length)};
            // Make sure the same two characters don't appear next to each other
            if ($r != $string{$i - 1})
                $string .= $r;
        }
        return $string;
    }

}
