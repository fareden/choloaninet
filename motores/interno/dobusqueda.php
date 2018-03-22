<?php
//session_start();
define('REGS_PAGINA','99999');
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
			$arrJSON = array("registros" => array(), "paginador" => "", "error" => "0");
			$key = (isset($_POST['b']) ? $_POST['b'] : "");
			$pag = $_POST['p'];
			$llave = esclarece($_POST['l']);
			$cond = "";
			//if (isset($_POST['c'])) $cond = $_POST['c'];
			if ($key != "") {
				$qry = "show full columns in vc_$tabla;";
				$rs = $dbcon->query($qry);
				while ($fila = $rs->fetch_row()) {
					if (!($fila[4] == 'PRI' && $fila[6] == 'auto_increment') && strpos($fila[1], "char") !== FALSE) {
						$cond .= "`$fila[0]` like ('%$key%') or ";
					}
				}
				$cond = substr($cond, 0, -4);
			}
			//Generar aquí el resultado en JSON...
			$arrJSON['registros'] = generaVista($tabla, $llave, $dbcon, $cond, $pag);
			$arrJSON['paginador'] = creaPaginador($tabla, $dbcon, $cond);
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

function generaVista($tabla, $edLlave, $dbcon, $condicion = "", $pag = 0) {
	//Esta función debe de cambiar por una consulta ajax desde el cliente...
	$retval = array();
	$pag = $pag * REGS_PAGINA;
	if ($condicion != "" || $edLlave != "") $condicion = " where " . ($edLlave != "" ? $edLlave : "") . ($condicion != "" ? " AND ($condicion)" : "");
	$qry="select * from vc_$tabla $condicion limit $pag, " . REGS_PAGINA . ";";
	error_log("Para buscar: $qry");
	//FIXIT quitar el query del die
	$result=$dbcon->query($qry);
	if ($result !== false) {
		$binarias = array();
		while ($infoCol = $result->fetch_field()) {
			if ($infoCol->type == 252 && $infoCol->flags & 128) {
				array_push($binarias, $infoCol->name);
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
