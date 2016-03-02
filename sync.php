<?php
require_once './fusiondirectory/jsonRPCClient.php'; // fichier venant de l'installation de FusionDirectory.
require_once './google/googleapi.php';
require_once './fusiondirectory/fusionapi.php';

// Instanciation API client and construct the service object.
$gapi = new googleapi();
$fusion = new fusionapi();
$clientGoogle = googleapi::getClient();
$service = new Google_Service_Directory($clientGoogle);

$GLOBALS['results'] = $service->users->listUsers(optParams);

//Initialisation des variables
$GLOBALS['googleTab'] = array();
$GLOBALS['fusionTab'] = array();
$GLOBALS['syncTab'] = array();


//------------------------------------------------------------------MAIN----------------------------------------------------------------------------------------------------------------
    
    //Step 1
    if (count($results->getUsers()) == 0) {
        print "No users found.\n";
    } else {
        searchUsers();
    }
    //Step 2
    fusionDir();
    //Step 3
    crudUser();

//------------------------------------------------------------------Fonctions-----------------------------------------------------------------------------------------------------------------------
  
    
/**
 * Recherche de l'ensemble des users
 */
function searchUsers() {
     print "Users:\n";
         foreach ($GLOBALS['results']->getUsers() as $user) {
         $googleUsers = array(
                'name' => $user->getName()->getGivenName(),
                'familyname' => $user->getName()->getFamilyName(),
                'mail' => $user->getPrimaryEmail(),
            );
            array_push($GLOBALS['googleTab'], $googleUsers);
        }  
}

/**
 * Récupération des utilisateurs FusionDirectory
 */
function fusionDir() {
            
                //Instanciation 
                $client = fusionapi::connecRPCClient();
              
                /* Then we need to login. Here we log in the default LDAP */
                $session_id = $client->login(NULL, login, password);

                $attr = array(
                    'mail' => 1,
                    'givenName' => 1,
                    'sn' => 1,
                    'uid' => 1,
                );

                /* Once we have a session ID, we can ask for the list of users */
                $users = $client->ls($session_id, 'user', $attr);

                foreach ($users as $dn => $user) {
                    if (isset($user['mail'])) {
                        $fdMail = $user['mail'];
                    } else {
                        $fdMail = '';
                    }

                    $fusionUsers = array(
                        'name' => $user['givenName'],
                        'familyname' => $user['sn'],
                        'mail' => $fdMail,
                    );
                    array_push($GLOBALS['fusionTab'], $fusionUsers);
                }
} 


/**
 * Fonction de modification et ajout d'utilisateur de Google Apps vers Fusion Directory
 */
function crudUser() {
    // merci kim !
        echo '----------------------';
        echo "\n";
        $nbr = 0;
        foreach ($GLOBALS['googleTab'] as $value1) {
            if (in_array($value1, $GLOBALS['fusionTab'])) {
                $nbr++;
                echo $value1['familyname'];
                echo '  -  ';
                echo 'Nothing to do';
                echo "\n";
            } else {
                $exist = FALSE;
                foreach ($GLOBALS['fusionTab'] as $valueFusion) {
                    if ($valueFusion['mail'] == $value1['mail']) {
                        $exist = TRUE;
                        echo 'Modifier user ';
                        echo $value1['mail'];
                        echo ' => ';
                        fusionapi::modifyUser($value1['name'], $value1['familyname'], $value1['mail']);
                        break;
                        }
                }
                if ($exist == FALSE) {
                    array_push($GLOBALS['syncTab'], $valueFusion);
                    echo 'Ajouter user ';
                    echo $value1['mail'];
                    //echo ' => ';
                    fusionapi::addUser($value1['name'], $value1['familyname'], $value1['mail']);
                }
                echo "\n";
            }
        }
}

// Remerciements : Kim, Antoine, Samia