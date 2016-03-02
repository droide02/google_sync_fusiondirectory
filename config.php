<?php
// connection au serveur Fusion Directory
define('host', 'http://127.0.0.1/fusiondirectory/jsonrpc.php');
define('ca_file', '/etc/ssl/certs/fd.pem');

// login fd-admin de Fusion Directory
define('login', 'fd-admin');
define('password', 'P@ssw0rd');

// DN of an existing user we can display and modify */
define('userdn', 'uid=fd-admin,ou=people,dc=societe,dc=com');

// Taille du mot de passe généré
define('pwd_length', 12);

//
const ssl_options = array(
    'cafile' => ca_file,
    'peer_name' => 'localhost',
    'verify_peer' => TRUE,
    'verify_peer_name' => TRUE,
	);

//
const http_options = array(
    'timeout' => 10
	);

//// Print the first 500 users in the domain.
const optParams = array(
    'customer' => 'my_customer',
    'maxResults' => 500,
    'orderBy' => 'email',
);

?>
