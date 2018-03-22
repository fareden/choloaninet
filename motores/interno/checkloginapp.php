<?php
//session_start();
require_once("./defs.php");
require_once("./funciones.php");
require_once("./conexion.php");
if (isset($_POST['token']) && esclarece($_POST['token']) == "hanumappelpoderoso!") {
    $dbcon = conectaDB();
    $retval = array("error" => "1", "errmsg"=>"Error masivo");
    //Tal como está configurado, saca la sesión de otro lugar una vez que ingrese.
    $consulta = $dbcon->prepare("SELECT idusuario, rol, nombrecompleto, permisos, pagina_inicial FROM virt_usuario WHERE email = ? and passwd=password(?);");
    $consulta->bind_param("ss", $_POST['usr'], $_POST['pwd']);
    $consulta->execute();
    //$consulta->store_result();
    $consulta->bind_result($id, $rol, $nombre, $permisos, $pag);
    $privada = "";
    $retval["error"] = "1";
    $retval["errmsg"] = "Error credenciales";
    while ($consulta->fetch()) {
        //Generar la llave pública y privada, y guardar la sesión en la base de datos
        $configSSL = array('private_key_bits' => 512);
        $res = openssl_pkey_new($configSSL);
        openssl_pkey_export($res, $privada);
        $publica = openssl_pkey_get_details($res);
        $publica = $publica['key'];
        $retval["error"] = "0";
        $retval["pagina"] = $pag;
        $retval["id"] = $id;
        $retval["rol"] = $rol;
        $retval["nombre"] = $nombre;
        $retval["perms"] = $permisos;
        $retval["publica"] = $publica;
    }
    $consulta->close();
    if ($retval["error"] == "0") {
        $qry = "update virt_usuario set privada = '{$privada}' where idusuario = '{$id}';";
        if ($dbcon->query($qry)) error_log("Dice que pudo poner la privada");
        else error_log("Dice que: " . $dbcon->error);
    }
    $dbcon->close();
    echo json_encode($retval);
} else {
    header('HTTP/1.0 404 Not Found', true, 404);
    //var_dump($_POST);
    die();
}
?>