<?php
//session_start();
require_once("./defs.php");
require_once("./funciones.php");
require_once("./conexion.php");
if (isset($_POST['token']) && esclarece($_POST['token']) == "elpoderosohanumat") {
	$dbcon = conectaDB();
	header('Content-Type: text/html; charset=utf-8');
	//error_log(print_r($_POST, true));
	//Tal como está configurado, saca la sesión de otro lugar una vez que ingrese.
	$consulta = $dbcon->prepare("SELECT idusuario, rol, nombrecompleto, permisos, pagina_inicial FROM virt_usuario WHERE email = ? and passwd=password(?);");
	$consulta->bind_param("ss", $_POST['usr'], $_POST['pwd']);
	$consulta->execute();
	//$consulta->store_result();
	$consulta->bind_result($id, $rol, $nombre, $permisos, $pag);
	$privada = "";
	$retval = '{"error":"1", "errmsg":"No se pudo iniciar sesión"}';
	while ($consulta->fetch()) {
		//Generar la llave pública y privada, y guardar la sesión en la base de datos
		$configSSL = array('private_key_bits' => 512);
		$res = openssl_pkey_new($configSSL);
		openssl_pkey_export($res, $privada);
		$publica = openssl_pkey_get_details($res);
		$publica = str_replace("\n", "\\n", $publica['key']);
		$retval = '{"error":"0", "pagina":"'.$pag.'", "id":"'.$id.'", "rol":"'.$rol.'","nombre":"'.$nombre.'", "perms":"'.$permisos.'","publica":"'.$publica.'"}';
	}
	$consulta->close();
	if (strpos($retval, '"error":"0"')) {
		$qry = "update virt_usuario set privada = '{$privada}', ultimo_login = now() where idusuario = '{$id}';";
		if ($dbcon->query($qry)) error_log("Dice que pudo poner la privada");
		else error_log("Dice que: " . $dbcon->error);
	}
	$dbcon->close();
	echo $retval;
} else {
	header('HTTP/1.0 404 Not Found', true, 404);
	//var_dump($_POST);
	die();
}
?>