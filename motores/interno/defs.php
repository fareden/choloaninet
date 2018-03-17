<?php
$TIPOS_IMAGEN = array("image/jpeg", "image/gif", "image/png");
$TIPOS_DOCUMENTO = array("application/pdf");
const PRODUCCION = FALSE;

define("SECRETO_RECAPTCHA", "6LcQ5goTAAAAAEME6Vv_58-2tMZMnhHTEa4qZHOe");
define("DOMINIO", "https://lab.achichincle.net", false);
define("MOVILAPP", "file:///", false);
define("SUBIDAS", "/var/tmp/", false);
define("PAG_DEFAULT", "./index.php", false);
define("CORREO_ORIGEN", "Pruebas Carpathia <carpathiapruebas@gmail.com>", FALSE);
define("CORREO_SERVIDOR", "ssl://smtp.gmail.com", FALSE);
define("CORREO_USUARIO", "carpathiapruebas@gmail.com", FALSE);
define("CORREO_PASSWD", "orgon2016", FALSE);
define("CORREO_PUERTO", "465", FALSE);
define("NOMBRE_APLICACION", "Zenbakia", FALSE);

//TODO El ejército Cuadrumano es un arreglo con las posibles implementaciones de hanumat en diferentes servidores.
$ejercitoCuadrumano = array("./motores/hanumat.php");
?>
