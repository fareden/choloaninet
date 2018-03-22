<?php
require_once('defs.php');
require_once('conexion.php');
require_once('funciones.php');
$retval = "";
error_log("AUTOCOMPLETAR iniciando...");
if (isset($_POST['token'])) {
    header('Content-Type: text/html; charset=utf-8');
    if ($dbcon = conectaDB()) {
        if (validaToken($_POST['token'], $dbcon, $_POST['r'], $_POST['idU'])) {
            //Aquí empieza lo gordo...
            $tabla = esclarece($_POST['t']);
            $arrJSON = array("registros" => array(), "error" => "0");
            //select idalumno, concat(alumnos.Nombre, ' ', alumnos.Apellidos) from alumnos where concat_ws(' ', nombre, apellidos) like ('%usua%') collate utf8_general_ci;
            $listado = esclarece($_POST['l']);
            $listado = explode("|", $listado, 2);
            $qry = "select $listado[0] as value, $listado[1] as label from $tabla where " . resuelveCond($_POST['c']) ." and $listado[1] like('%{$_POST['te']}%') collate utf8_general_ci limit 50;";
            if ($rs = $dbcon->query($qry)) {
                while ($fila = $rs->fetch_assoc()) {
                    array_push($arrJSON['registros'], $fila);
                }
            } else {
                $arrJSON['error'] = '15';
                $arrJSON['errmsg'] = "Consulta fallida: $qry";
            }
            $retval = json_encode($arrJSON);
        } else {
            $retval = '{"error":"14", "errmsg":"Token inválido"}';
        }
    } else {
        $retval = '{"error":"21", "errmsg":"No hay datos"}';
    }
} else {
    //Petición incorrecta
    $retval = '{"error":"11", "errmsg":"Sesión inválida"}';
}
echo($retval);
?>