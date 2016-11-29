<?php

// Place all your configuration parameters in this structure.
// Optionally, place the data in a file outside of this directory.
$gConfigDB = array(
	'dbname'=>'database_name', 
	'username'=>'a_user_with_full_rights_to_the_above_db',
	'password'=>'secret_password', 
	'jwt_secret'=>'random_string_used_to_generate_tokens'
);

// Any global variable called $gConfigDB in the file below will overwrite 
// the values above. 
// We have a config outside of the accessible web server filesystem, for
// security.
include_once '/etc/migrainealert/config.php';
?>
