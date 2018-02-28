<?php
function conectaDB() {
	$servidor = 'localhost';
	$dbnom = 'migrantech';
	$usuario = 'usrcholoani';
	$password = 'ch0l04n1N3T2018';
	$puerto = 3714;
	$conectID=new mysqli($servidor, $usuario, $password, $dbnom, $puerto);
	if(!$conectID->connect_error) {
		$conectID->set_charset('utf8');
		return $conectID;
	} else {
		return null;
	}
}
?>
