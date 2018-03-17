<?php
//Sirve para dar de alta usuarios desde el autofirmado...
require_once("./defs.php");
require_once("./funciones.php");
require_once("./conexion.php");
if (isset($_POST['token']) && esclarece($_POST['token']) == "elpoderosohanumat") {
    //antes de seguir validamos que tenga datos en todo...
    $pasa = TRUE;
    foreach($_POST as $val) {
        $pasa = $val == "" ? $pasa && false : $pasa && true;
    }
    if ($pasa) {
        $dbcon = conectaDB();
        //Tal como está configurado, saca la sesión de otro lugar una vez que ingrese.
        $consulta = $dbcon->prepare("SELECT usuarioAutoservicio(?, ?, ?, ?, ?, ?, ?);");
        $consulta->bind_param("sssssss", $_POST['no'], $_POST['ap'], $_POST['am'], $_POST['idu'], $_POST['contras'], $_POST['fn'], $_POST['sx']);
		error_log(print_r($_POST, true));
        $consulta->execute();
        $consulta->bind_result($ret);
        $retval = '{"error":"1", "errmsg":"No se pudo registrar"}';
        error_log("Entrando a validar...");
        while ($consulta->fetch()) {
            //Generar la llave pública y privada, y guardar la sesión en la base de datos
            error_log("respuesta: $ret");
            if ($ret > "0")
                $retval = '{"error":"0", "idnuevo":"{$ret}"}';
        }
        $consulta->close();
        //$rs->free();
        echo $retval;
    } else {
        header('HTTP/1.0 404 Not Found', true, 404);
        //var_dump($_POST);
        die();
    }
} else {
    header('HTTP/1.0 404 Not Found', true, 404);
    //var_dump($_POST);
    die();
}
?>