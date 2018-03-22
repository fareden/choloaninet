<?php
require_once('defs.php');
set_include_path('/usr/share/php');
require_once('Mail.php');
require_once('Mail/mime.php');
function enviamsg($mensaje, $destino, $asunto) {
	$headers = array ('From' => CORREO_ORIGEN, 'To' => $destino, 'Subject' => $asunto, 'Content-Type' => 'text/html; charset=UTF-8');
    $mime = new Mail_mime(array('eol' => "\r\n", 'text_encoding' => 'utf8', 'html_encoding' => 'utf-8', 'text_charset' => 'utf-8', 'head_charset'=>'utf-8', 'html_charset' => 'utf-8'));
    $mime->setTXTBody("Para ver mejor este mensaje, active el HTML. $mensaje");
    $mime->setHTMLBody($mensaje);
    $cuerpo = $mime->get();
    $enc = $mime->headers($headers);
	$smtp = Mail::factory('smtp', array ('host' => CORREO_SERVIDOR, 'port' => CORREO_PUERTO, 'auth' => true, 'username' => CORREO_USUARIO, 'password' => CORREO_PASSWD));
	$correo = $smtp->send($destino, $enc, $cuerpo);
	if (PEAR::isError($correo)) {
		error_log($correo->getMessage());
        return false;
	} else {
		return true;
	}
}
?>