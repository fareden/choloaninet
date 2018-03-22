<?php 
//Cambios que estamos haciendo:
require_once('./defs.php');
require_once('./funciones.php');
require_once('./conexion.php');
$retval = "";
if (isset($_POST['token'])) {
	header('Content-Type: text/html; charset=utf-8');
	if ($dbcon = conectaDB()) {
		if (validaToken($_POST['token'], $dbcon, $_POST['r'], $_POST['idU'])) {
			//antes vamos a validar el recaptcha
			if (isset($_POST['g-recaptcha-response'])) {
				//Una vez más, es un usuario peligroso...
				$url = 'https://www.google.com/recaptcha/api/siteverify';
				$datos = array('secret' => SECRETO_RECAPTCHA, 'response' => $_POST['g-recaptcha-response'], 'remoteip' => '');
				$opt = array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($datos)));
				$contexto = stream_context_create($opt);
				$resultado = file_get_contents($url, false, $contexto);
				//El resultado es un JSON
				$json_res = json_decode($resultado, true);
				//Vamos a ver si tuvo éxito la lectura:
				if ($json_res['success'] == false) {
					//No fue, cerramos sesión y matamos esto...
					session_destroy();
					//Por lo pronto y para depuración, de otro modo redireccionamos a index...
					die('{"error":"10", "errmsg":"Error RECAPTCHA"}');
				}
			}
			$tabla=$_POST['r'];
			$exito = true;
			$paso = "";
			$exclusiones = array('r','ae','g-recaptcha-response','idU','token', 'alta');
			foreach ($_POST as $llave => $valor) {
				if (is_array($valor)) {
					//Ya sabemos que es detalle...
					foreach ($valor as $v) {
						$paso .= esclarece($llave) ."[]=$v&";
					}
				} else {
					if (!in_array($llave, $exclusiones)) {
						//En este punto tenemos que esclarecer las variables que vienen ofuscadas
						$paso .= esclarece($llave) . "={$valor}&";
					}
				}
				//Esta es la línea donde ponemos las excepciones, o sea, lo que no sea un campo
			}
			error_log("Valor en PASO: $paso");
			parse_str($paso, $arrCampos);
			$tblsMax = array();
			if (isset($_POST['ae']) && $_POST['ae'] != '') {
				//esclarecemos...
				$arrEdita = explode("|", $_POST['ae']);
				$claro = esclarece($arrEdita[0]);
				$modo = substr($claro, 0, 1);
				$arrEdita[0] = substr($claro, 1);
				switch ($modo) {
					case '1':	//Estamos hablando de la edición de un registro...
						$esdetalle = false;
						foreach ($arrCampos as $llave => $valor) {
							$qrycampos = "update " . $llave . " set ";
							$qryDetC = "";
							$qryDetV = "";
							foreach ($arrCampos[$llave] as $lakey => $elval) {
								if (substr($llave, 0, 2) == '__') {
									$qryDetC = "INSERT IGNORE INTO ".str_replace('__', '', $llave)." (";
									$vals = procesaDetalle($elval, $qryDetC, $dbcon, $arrEdita[1], str_replace('__', '', $llave));
									$qryDetC = $vals['campos'];
									$qryDetV .= $vals['valores'];
									$esdetalle = true;
									//error_log(print_r($vals, true));
								} else {
									$qrycampos .= armaCampo('1', $lakey, $elval, $dbcon);
									$esdetalle = false;
								}
							}
							if ($esdetalle) {
								$qryDetC = substr_replace($qryDetC, ') ', -1, 1);
								$qryDetV = "VALUES (" . substr_replace($qryDetV, ';', -2, 2);
								$qrycampos = $qryDetC . $qryDetV;
								error_log($qrycampos);
							} else {
								$qrycampos = substr_replace($qrycampos, ' ', -1, 1) . "where $arrEdita[0]='$arrEdita[1]'";
							}
							if (bitacora($qrycampos, $dbcon) && $dbcon->query($qrycampos)) {
								$exito = ($exito && true);
							} else {
								error_log($qrycampos);
								$exito = ($exito && false);
							}
						}
						break;
					case '2':	//Vamos a borrar un registro...
						//Excepción para la tabla de anuncios...
						$tblborra = esclarece($arrEdita[2]);
						if (puedeBorrar($tblborra, $dbcon)) {
							$qry = "DELETE from " .$tblborra." where $arrEdita[0] = '$arrEdita[1]';";
						} else {
							$qry = "UPDATE " .$tblborra." set activo = '0' where $arrEdita[0] = '$arrEdita[1]';";
						}
						//Sanitizar query...
						error_log("Para borrar: $qry");
						if (bitacora($qry, $dbcon) && $dbcon->query($qry)) {
							$exito = ($exito && true);
						} else {
							error_log($qry);
							$exito = ($exito && false);
							$retval = '{"error":"14", "errmsg":"Borrado ilegal"}';
						}
						break;
				}
			} else {
				foreach ($arrCampos as $llave => $valor) {
					$qrycampos = "";
					$qryvalores = "";
					//TODO Anexar el arreglo de los máximos ID que se van a traer... va a ser lento, pero no hay otra opción.
					$qrycampos = "INSERT IGNORE INTO ".str_replace('__', '', $llave)." (";
					//if ($tabla == "dameID") array_push($tblsMax, $llave);
					foreach($valor as $campo => $elval) {
						if (substr($llave, 0, 2) == '__') {
							//Aquí va el detalle...
							$esdetalle = true;
							$vals = procesaDetalle($elval, $qrycampos, $dbcon);
							$qrycampos = $vals['campos'];
							$qryvalores .= $vals['valores'];
						} else {
							$esdetalle = false;
							$qrycampos .= $campo . ",";
							$qryvalores .= armaCampo('0', $campo, $elval, $dbcon);
						}
					}
					$qrycampos = substr_replace($qrycampos, ') ', -1, 1);
					if ($esdetalle) {
						$qryvalores = "VALUES (" . substr_replace($qryvalores, ';', -2, 2);
					} else {
						$qryvalores = "VALUES (" . substr_replace($qryvalores, ');', -1, 1);
					}
					
					$qry = $qrycampos . $qryvalores;
					if (bitacora($qry, $dbcon) && $dbcon->query($qry)) {
						$exito = ($exito && true);
						//error_log("Ejecutado chido: $qry");
					} else {
						error_log("Consulta fallida: $qry");
						$exito = ($exito && false);
					}
				}
			}
			if ($exito) {
				//echo("dijo que fifó...");
				if (isset($_POST['alta'])) {
					//TODO Quitar también de la base de datos...
					session_destroy();
				}
				if ($tabla == 'dameID') {
					//FIXME Cambiar por select por tabla
					/*
					foreach ($tblsMax as $t) {
						$qry = "select max()"
					}
					 * 
					 */
					$qry = "select last_insert_id();";
					if ($rs = $dbcon->query($qry)) {
						$fila = $rs->fetch_row();
						$retval = '{"error":"0", "id":"'.$fila[0].'"}';
					}
				} else {
					$retval = '{"error":"0", "destino":"'.$tabla.'"}';
				}
			} else {
				$retval = '{"error":"13", "errmsg":"Consulta con problemas"}';
			}
		} else {
			$retval = '{"error":"14", "errmsg":"Token inválido"}';
		}
	} else {
		$retval = '{"error":"12", "errmsg":"Problemas de base de datos"}';
	}
} else {
	$retval = '{"error":"11", "errmsg":"Sesión inválida"}';
}
echo $retval;

function borraDetalle($tabla, $llave, $valor, $dbcon) {
	$qry = "delete from $tabla where $llave = $valor;";
	if (bitacora($qry, $dbcon) && $dbcon->query($qry)) {
		error_log("Se limpia la tabla de detalle $tabla");
	} else {
		error_log("Consulta de limpieza fallida: $qry");
	}
	
}

function procesaDetalle($elval, $qrycampos, $dbcon, $idEdita = "", $tbl = "") {
	$retval = array("campos" => "", "valores" => "");
	$laLlave = "";
	$cols = explode("|-|", $elval);
	//Tenemos aquí todas las columnas.
	foreach($cols as $col) {
		if ($col != "") {
			$campo = esclarece(substr($col, 0, strpos($col, '|')));
			if (!strpos($qrycampos, $campo, strpos($qrycampos, "("))) {
				$qrycampos .= $campo . ",";
				//error_log(count($cols) . " - " . $col);
				if ($cols[count($cols) -2] == $col) $laLlave = $campo;
			}
			$retval['valores'] .= armaCampo('0', $campo, substr($col, strpos($col, '|') + 1), $dbcon);
		}
	}
	if ($idEdita != "") borraDetalle($tbl, $laLlave, $idEdita, $dbcon);
	$retval['valores'] = substr($retval['valores'], 0, -1) . '),(';
	$retval['campos'] = $qrycampos;
	return $retval;
}

function armaCampo($tipo, $nombre, $valor, $dbcon) {
	$retval = "";
	$valor = $dbcon->real_escape_string($valor);
	switch($tipo) {
		case '0':
			if (strpos($valor, "geo||") > -1) {
				//Apartado especial para los objetos geográficos
				$obj = substr($valor, 5, 2);
				$valor = substr($valor, 7);
				switch ($obj) {
					case "pu":
						$valor = "geomfromtext('POINT({valor})')";
						break;
					case "li":
						$valor = "geomfromtext('LINE({valor})')";
						break;
					case "ml":
						$valor = "geomfromtext('MULTILINESTRING({valor})')";
						break;
				}
				$retval .= $valor . ",";
			} else if ($nombre == 'passwd' || $nombre == 'pwd') {
				$retval .= 'password(\'' . $valor . '\'),';
			} else if (strpos($valor, "cache||") > -1) {
				//Obtenemos el nombre del cache...
				$nomfile = substr($valor, strpos($valor, "||") + 2);
				$fp = fopen($nomfile, 'r');
				$data = fread($fp, filesize($nomfile));
				$data = addslashes($data);
				fclose($fp);
				$retval .= "'$data',";
			} else if (strpos($valor, ";base64,") > -1) {
				error_log("Es una imagen en base64 **0**...");
				$data = addslashes(base64_decode(str_replace(' ', '+', substr($valor, strpos($valor, ",") + 1))));
				$retval .= "'$data',";
			}else {
				$retval .= ($valor == "now()" ? "now()," : "'$valor',");
			}
			break;
		case '1':   //Actualización:
			$retval .= $nombre . "=";
			if (strpos($valor, "geo||") > -1) {
				//Apartado especial para los objetos geográficos
				$obj = substr($valor, 5, 2);
				$valor = substr($valor, 7);
				switch ($obj) {
					case "pu":
						$valor = "geomfromtext('POINT({valor})')";
						break;
					case "li":
						$valor = "geomfromtext('LINE({valor})')";
						break;
					case "ml":
						$valor = "geomfromtext('MULTILINESTRING({valor})')";
						break;
				}
				$retval .= $valor . ",";
			} else if ($nombre == 'passwd' || $nombre == 'pwd') {
				//$crVal = md5($elval);
				//$qrycampos .= "'$crVal',";
				$retval .= "password('$valor'),";
			} else if (strpos($valor, "cache||") > -1) {
				//Obtenemos el nombre del cache...
				$nomfile = substr($valor, strpos($valor, "||") + 2);
				$fp = fopen($nomfile, 'r');
				$data = fread($fp, filesize($nomfile));
				$data = addslashes($data);
				fclose($fp);
				$retval .= "'$data',";
			} else if (strpos($valor, ";base64,") > -1) {
				error_log("Es una imagen en base64 **1**...");
				error_log(substr($valor, strpos($valor, ",") + 1, 40));
				$data = addslashes(base64_decode(str_replace(' ', '+', substr($valor, strpos($valor, ",") + 1))));
				$retval .= "'$data',";
			} else {
				$retval .= ($valor == "now()" ? "now()," : "'$valor',");
			}
			break;
	}
	return $retval;
}

function puedeBorrar($tabla, $dbcon) {
	$qry = "show grants;";
	$rs = $dbcon->query($qry);
	$retval = false;
	if ($rs !== false) {
		while ($fila = $rs->fetch_row()) {
			//error_log("evaluando: $fila[0]");
			$retval = (strpos($fila[0], "`{$tabla}`") !== false && strpos($fila[0], "DELETE"));
			if ($retval) break;
		}
	}
	return $retval;
}

function bitacora($accion, $dbcon) {
	if (defined('REGISTRA_BITACORA')) {
		$accion = strtolower($accion);
		error_log(substr($accion, 0, 6) == "update");
		if (substr($accion, 0, 6) == "update") $acc =  'Actualización'; else $acc = (strpos($accion, "insert ignore into") === false ? 'Borrado' : 'Agregado');
		if (substr($accion, 0, 6) == "update") $tbl = substr($accion, 7, strpos($accion, "set") -8); else $tbl = strpos($accion, "insert ignore into") === false ? trim(substr($accion, 12, strpos($accion, "where") -13)) : trim(substr($accion, 19, strpos($accion, "(") -20));
		$qry = "insert into bitacora (idusuario, accion, tabla) values ({$_POST['idU']}, '{$acc}', '{$tbl}');";
		error_log("Consulta de bitácora: $qry");
		if ($dbcon->query($qry)) return TRUE;
	} else {
		return TRUE;
	}
}
?>