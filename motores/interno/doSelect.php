<?php
require_once('defs.php');
require_once('conexion.php');
require_once('funciones.php');
$retval = "";
if (isset($_POST['token'])) {
    header('Content-Type: text/html; charset=utf-8');
    if ($dbcon = conectaDB()) {
        if (validaToken($_POST['token'], $dbcon, $_POST['r'], $_POST['idU'])) {
            $tabla = esclarece($_POST["t"]);
            $qry="select " . esclarece($_POST["l"]) . " from $tabla where " . resuelveCond($_POST["c"]) . ";";
            $result = $dbcon->query($qry);
            //Tenemos una sola fila...
            if ($result->num_rows == 1) {
                $fila = $result->fetch_array();
                $retval = '';
                //Vamos a crear el JSON...
                $cols = $result->fetch_fields();
                $arrRet = array("error" => "0");
                //$retval = '{"error":"0",';
                foreach ($cols as $col) {
                    //error_log("Datos del campo {$col->name}: {$col->type} : {$col->flags}");
                    if ($col->type == 252 && $col->flags & 128) {
                        //FIXIT traer / identificar el MIME para enivarlo...
                        $mime = $fila[$col->name . "_mime"];
                        //error_log("La imagen: " . print_r($fila[$col->name], TRUE));
                        $arrRet[ofusca($tabla . '_' . $col->name)] = 'data:' . $mime . ';base64,'.base64_encode($fila[$col->name]);
                        //$retval .= '"' . ofusca($tabla . '_' . $col->name).'": "data:' . $mime . ';base64,'.base64_encode($fila[$col->name]).'",';
                        //error_log("Cuando pone la imagen: $retval");
                    } else {
                        $arrRet[ofusca($tabla . '_' . $col->name)] = $fila[$col->name];
                        //$retval .= '"' . ofusca($tabla . '_' . $col->name).'": "'.$fila[$col->name].'",';
                    }
                }
                $retval = json_encode($arrRet);
                //$retval = substr($retval, 0, -1) . "}";
            } else {
                error_log("Consulta fallida al seleccionar: $qry");
                $retval = '{"error":"21", "errmsg":"No hay datos"}';
            }
        } else {
            //Error: token o sesión inválida
            $retval = '{"error":"14", "errmsg":"Token inválido"}';
        }
    } else {
        //Error con la base de datos
        $retval = '{"error":"12", "errmsg":"Problemas de base de datos"}';
    }
} else {
    //Petición incorrecta
    $retval = '{"error":"11", "errmsg":"Sesión inválida"}';
}
echo $retval;
?>
