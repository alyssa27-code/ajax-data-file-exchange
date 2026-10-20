<?php

	$hostname = 'localhost';
	$username = 'root';
	$password = '';
	$dbname = 'ajax_prove_upload';
	$port = 3306;

	$conn = new mysqli($hostname, $username, $password, $dbname, $port);
	if($conn->errno){
		die("Errore di connessione --> " . $conn->error);
	}
	
?>
