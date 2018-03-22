<?php
//require_once('interno/defs.php');
function esMovil() {
	return preg_match("/(kalli_app|android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function ofusca($texto) {
    $datos = gzcompress($texto, 9);
    $retval = base64_encode($datos);
    return $retval;
}

function esclarece($datos) {
    $dat = base64_decode($datos);
    $retval = gzuncompress($dat);
    return $retval;
}

function resuelveCond($datos) {
    $arrMult = explode(",", $datos);
    $retval = "";
    foreach($arrMult as $parte) {
        if ($parte != "") {
            $arrCond = explode("|", $parte, 2);
            $campo = esclarece($arrCond[0]);
            if (substr($campo, 0, 1) == '*') {
                $retval = "`". substr($campo, 1) . "` like ('%{$arrCond[1]}%') and ";
            } else if (substr($campo, 0, 1) == '-') {
                $cr = explode("><", $arrCond[1], 2);
                $retval = "(`" . substr($campo, 1) . "` >= '{$cr[0]}' and `" . substr($campo, 1) . "` <= '{$cr[1]}') and ";
            } else {
                $retval .= esclarece($arrCond[0]) . " in ('{$arrCond[1]}') and ";
            }
        }
    }
    error_log("Condición obtenida: $retval");
    $retval = substr($retval, 0, -5);
    return $retval;
}

function validaToken($token, $dbcon, $r, $idusr) {
    //Validamos el token de sesión antes de seguir adelante
    //TODO validar que exista la sesión en la base de datos.
    //$_POST['r'] . '|' . DOMINIO . '|' . $_SESSION['Usuario']['id']
    //Traemos la llave privada de la DB
    $retval = FALSE;
    $qry = "SELECT privada FROM virt_usuario WHERE idusuario = '{$idusr}';";
    //error_log("Intentando token estricto");
    $rs = $dbcon->query($qry);
    if ($rs) {
        $fila = $rs->fetch_row();
		error_log($token);
        openssl_private_decrypt($token, $res, $fila[0]);
        //error_log("Resultado después de desencriptar el token: $res");
        $retval = ($res == $r . '|' . DOMINIO . '|' . $idusr);
    } else {
        //error_log("Base de datos dijo: " . $dbcon->error);
    }
    return $retval;
}

function dameCuadrumano() {
    //Devuelve un string con el siguiente cuadrumano disponible.
    if (count($ejercitoCuadrumano) < 2) {
        return $ejercitoCuadrumano[0];
    } else {
        //TODO random seed para número entre 0 y cuadrumanos - 1, devolvemos ese índice...
        
    }
}
?>