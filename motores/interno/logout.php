<?php
require_once("./conexion.php");
if (isset($_POST['idU'])) {
    $dbcon = conectaDB();
    //Tal como está configurado, saca la sesión de otro lugar una vez que ingrese.
    $consulta = $dbcon->prepare("update virt_usuario set privada = null where idusuario = '{$_POST['idU']}';");
    $consulta->execute();
    $retval = '{"error":"0"}';
    $consulta->close();
    $dbcon->close();
    echo $retval;
} else {
    header('HTTP/1.0 404 Not Found', true, 404);
    //var_dump($_POST);
    die();
}

?>