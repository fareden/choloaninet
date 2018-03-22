<?php
//session_start();
require_once('defs.php');
require_once('conexion.php');
require_once('funciones.php');
$retval = "";
if (isset($_POST['token'])) {
    header('Content-Type: text/html; charset=utf-8');
    if ($dbcon = conectaDB()) {
        if (validaToken($_POST['token'], $dbcon, $_POST['r'], $_POST['idU'])) {
            //Aquí empieza lo gordo...
            $tabla = esclarece($_POST['t']);
            $arrJSON = array("registros" => array(), "error" => "0");
            $llave = esclarece($_POST['l']);
            $cond = resuelveCond($_POST['c']);
            //Generar aquí el resultado en JSON...
            $arrJSON['registros'] = generaVista($tabla, $llave, $dbcon, $cond);
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
echo $retval;

function generaVista($tabla, $campos, $dbcon, $condicion = "") {
    //Esta función debe de cambiar por una consulta ajax desde el cliente...
    $retval = array();
    $binarias = array();
    if ($condicion != "") $condicion = " where " . $condicion;
    $qry="select $campos from $tabla $condicion;";
	error_log("Consulta: $qry");
    $result=$dbcon->query($qry);
    if ($result !== false) {
        //error_log("Entrando a listar, consulta válida");
        while ($infoCol = $result->fetch_field()) {
            if ($infoCol->type == 252 && $infoCol->flags & 128) {
                array_push($binarias, $infoCol->name);
                error_log("Encuentra la columna {$infoCol->name} como binaria");
            }
        }
        while($fila = $result->fetch_assoc()) {
            foreach($binarias as $bin) {
                $fila[$bin] = 'data:' . $fila[$bin."_mime"] . ';base64,'.base64_encode($fila[$bin]);
            }
            array_push($retval, $fila);
        }
    }
    return $retval;
}
function creaPaginador($tabla, $dbcon, $condicion = "") {
    $retval = '<table><tr>';
    if ($condicion != "") $condicion = " where " . $condicion;
    $qry = "select count(*) from vc_$tabla $condicion;";
    $result = $dbcon->query($qry) or die($qry);
    $cnt = $result->fetch_row();
    $total = $cnt[0];
    $pags = ceil($total / REGS_PAGINA);
    for ($p = 0; $p < $pags; $p++) {
        $retval .= '<td><a href="javascript:void(0);" onclick="busca(' . $p . ')">' . ($p + 1) . '</a></td>';
    }
    $retval .= '</tr></table>';
    return $retval;
}


?>
