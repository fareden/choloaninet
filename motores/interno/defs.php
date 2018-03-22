<?php
$TIPOS_IMAGEN = array("image/jpeg", "image/gif", "image/png");
$TIPOS_DOCUMENTO = array("application/pdf");
const PRODUCCION = FALSE;

define("SECRETO_RECAPTCHA", "6LcQ5goTAAAAAEME6Vv_58-2tMZMnhHTEa4qZHOe");
define("DOMINIO", "https://lab.achichincle.net", false);
define("MOVILAPP", "file:///", false);
define("SUBIDAS", "/var/tmp/", false);
define("PAG_DEFAULT", "./index.php", false);
define("CORREO_ORIGEN", "", FALSE);
define("CORREO_SERVIDOR", "", FALSE);
define("CORREO_USUARIO", "", FALSE);
define("CORREO_PASSWD", "", FALSE);
define("CORREO_PUERTO", "", FALSE);
define("NOMBRE_APLICACION", "CholoaniNET", FALSE);

//TODO El ejército Cuadrumano es un arreglo con las posibles implementaciones de hanumat en diferentes servidores.
$ejercitoCuadrumano = array("./motores/hanumat.php");
?>
