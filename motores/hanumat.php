<?php
/**
 * Gestión intermedia de seguridad. Este es nuestro guardían de la puerta.
 * 
 */
//fail2ban
//modsecurity --apache módulo.
require_once('interno/defs.php');
require_once('interno/funciones.php');
if ((isset($_SERVER["HTTP_ORIGIN"]) && $_SERVER["HTTP_ORIGIN"] == DOMINIO) || strpos($_SERVER['HTTP_USER_AGENT'], "Firefox") !== FALSE) {
	session_start();
	//Aquí validamos que exista la variable de usuario, como override se puede establecer $hayUsuario = TRUE y luego poner valores arbitrarios.
	$hayUsuario = isset($_SESSION['Usuario']) && isset($_SESSION['Usuario']['publica']);
	if ($hayUsuario) {
		//Ya tenemos la primera validación en una mini-mierda de tiempo.
		//Segunda validación, viene de un origen permitido.
		//Ponemos un token de seguridad encriptado y seguimos adelante.
		if (isset($_POST['r'])) {
			//recordad, niños: r es la página de retorno, pero nos sirve también para saber que es una consulta y un montón de cosas más.
			header('Content-Type: application/json; charset=utf-8');
			if ($_POST['r'] != 'l') {
				openssl_public_encrypt($_POST['r'] . '|' . DOMINIO . '|' . $_SESSION['Usuario']['id'], $token, $_SESSION['Usuario']['publica']);
				$_POST['token'] = $token;
				$_POST['idU'] = $_SESSION['Usuario']['id'];
			} else if ($_POST['r'] == 's') {
				$_POST['idU'] = $_SESSION['Usuario']['id'];
			} else {
				$_POST['token'] = ofusca("elpoderosohanumat");
			}
			
			//Ya está lista: añadimos al post la clave, y vamos ahora sí por el doCatalogo.
			//Si estuviera en C++ podemos llamarlo desde aquí... el pedo sería el envío de datos binarios
			//TODO Aquí se puede extender para varios servidores...
			$serv = DOMINIO . str_replace('hanumat.php', 'interno', $_SERVER['REQUEST_URI']);
			$url = $serv;
			$res = "";
			switch ($_POST['r']) {
				case 'e':   //Seleccionar registros...
					$url .= '/doSelect.php';
					break;
				case 'l':   //Firmados e inicios de sesión
					if ($_SESSION['Usuario']['rol'] == 't')
						if (isset($_POST['f'])) {
							$url .= '/' . $_POST['f'];
						} else if (isset($_POST['fb'])) {
							if ($_POST['fb'] == '1')
								$url .= '/checkfblogin.php';
							else if ($_POST['fb'] == '2')
								$url .= '/nuevousrfb.php';
						} else {
							$url .= '/checklogin.php';
						}
					else if($_SESSION['Usuario']['rol'] == 'n')
						$url .= '/nuevousrpublico.php';
					break;
				case 'a':   //Autocompletar
					if (isset($_GET)) {
						$url = str_replace($_GET['callback'], "", $url);
						$url = str_replace("?callback=", "", $url);
						$_POST['cb'] = $_GET['callback'];
					}
					$url .= '/autocompletar.php';
					break;
				case 'b':
					$url .= '/dobusqueda.php';
					break;
				case 'c':   //Realmente será necesario?
					$url .= '/dolista.php';
					break;
				case 'x':   //Ejecuta un script;
					$url .= '/' . esclarece($_POST['f']);
					break;
				case 's':
					session_destroy();
					$url .= '/logout.php';
					break;
				case 'f':   //Archivo adjunto...
					//Los entredichos del archivo:
					//Se guarda en una carpeta (puede ser una carpeta compartida por NFS o algo así)
					$url = "";
					if (isset($_FILES)) {
						$res = guardaBinario($_FILES, (isset($_POST['cp']) && $_POST['cp'] == '1' ? TRUE : FALSE), (isset($_POST['re']) ? $_POST['re'] : 0), (isset($_POST['sm']) && $_POST['sm'] == '1' ? TRUE : FALSE));
					} else {
						//registrar el error
						$res = '{"error":"33", "errmsg":"Petición incoherente"}';
					}
					break;
				case 'r':
					
					break;
				default:
					$url .= '/doDB.php';
					break;
			}
			if ($url != "") {
				$opts = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($_POST)
					),
					'ssl' => array(
						"verify_peer"=>false,
						"verify_peer_name"=>false
					)
				);
				$contexto  = stream_context_create($opts);
				$res = file_get_contents($url, false, $contexto);
				//error_log($url);
			}
			if ($res !== FALSE) {
				$res = utf8_encode($res);
				error_log("Respuesta del login: ". print_r($res, true));
				$json = json_decode($res, false, 512, JSON_PARTIAL_OUTPUT_ON_ERROR);
				error_log("Decodificado: " . print_r($json, true));
				error_log(print_r(json_last_error_msg(), true));
				if ($json->error == '0') {
					//Tenemos un query exitoso
					if ($_POST['r'] == 'l' && !($_SESSION['Usuario']['rol'] == 'n' || isset($_POST['f']) || (isset($_POST['fb']) && $_POST['fb'] == '2'))) {
						parse_str($json->perms, $perms);
						$_SESSION['Usuario'] = array("id" => $json->id, "rol" => $json->rol, "nombre" => $json->nombre, "permisos" => $perms, "publica" => $json->publica, "pag_inicial" => $json->pagina);
						echo('{"error":"0", "pagina":"'.$json->pagina.'"}');
					} else if ($_POST['r'] == 'l' && $_SESSION['Usuario']['rol'] == 'n') {
						unset($_SESSION['Usuario']);
						session_destroy();
						echo($res);
					} else if (isset($_POST['cb'])) {
						echo("{$_POST['cb']}($res)");
					} else {
						echo($res);
					}
				} else if ($json->error == '21') {
					//Error en el engine de datos
					registraError("Error interno: {$json->error} - {$json->errmsg}", 10);
					echo('{"error":"1", "errmsg":"No hay información disponible"}');
				} else {
					registraError("Error interno: {$json->error} - {$json->errmsg}", 10);
					echo('{"error":"'.$json->error.'", "errmsg":"'.$json->errmsg.'"}');
				}
			} else {
				registraError("FALLO DE CONEXIÓN", 70);
			}
		} else {
			//Error validando el query
			registraError("CONSULTA INVÁLIDA", 90);
		}
	} else {
		//No hay usuario
		registraError("SESIÓN INVÁLIDA", 90);
	}
} else {
	//Le falta barrio
	error_log("Origen detectado: " . $_SERVER["HTTP_ORIGIN"]);
	registraError("FUERA DE DOMINIO", 100);
}

function registraError($tipo, $severidad) {
	//Volcamos el error al archivo en disco, o lo enviamos al manejador de errores
	//TODO Aquí va la línea del manejador de errores y firewall
	//A mayor severidad, más problemas
	error_log("Hanumat haciendo su trabajo: $tipo, $severidad");
}

function guardaBinario($arrArchivos, $conCoordenadas = false, $redimensiona = 0, $quitaMetadatos = true) {
	//Necesitamos saber los tipos válidos de archivos...
	$TIPOS_IMAGEN = array("image/jpeg", "image/gif", "image/png");
	$TIPOS_DOCUMENTO = array("application/pdf");
	$arrValidos = array_merge($TIPOS_IMAGEN, $TIPOS_DOCUMENTO);
	$retval = '{';
	foreach ($arrArchivos as $archivo) {
		if (in_array($archivo["type"], $arrValidos) && $archivo["error"] == 0) {
			//Todo bien...
			$dest = SUBIDAS . md5(uniqid().time());
			if (move_uploaded_file($archivo["tmp_name"], $dest)) {
				//preprocesamientos...
				if (in_array($archivo["type"], TIPOS_IMAGEN)) $retval .= procesaImagen($dest, $conCoordenadas, $redimensiona, $quitaMetadatos);
				else $retval .= '"preview":"img/'.str_replace("/", "-", $archivo["type"]).'.png",';
				//TODO en caso necesario, preprocesamos otros documentos
				$retval .= '"archivo":"cache||'.$dest.'","mime":"'.$archivo["type"].'","error":"0"}';
			} else {
				$retval = '{"error":"31", "errmsg":"No pudo moverlo"}';
			}
		} else {
			$retval = '{"error":"32", "errmsg":"Tipo de archivo inválido '.$archivo['type'].'"}';
		}
	}
	return $retval;
}
/*
function procesaArchivo($archivo, $procesador = null) {
	$retval = "";
	if ($procesador != null) {
		$res = shell_exec("$procesador \"$archivo\"");
		$retval = '"registros":'.$res;
	}
	return $retval;
}
 * 
 */
function procesaImagen($dest, $conCoordenadas = false, $redimensiona = 0, $quitaMetadatos = true) {
	$laImg = new Imagick($dest);
	$retval = "";
	if ($redimensiona != 0) {
		$laImg->resizeimage($redimensiona, $redimensiona, Imagick::FILTER_POINT, 0.9, true);
	}
	if (isset($arrExif['Orientation'])) {
		//Vamos a ver qué pedo con la orientación...
		switch($arrExif['Orientation']) {
			case 3:
				$laImg->rotateimage(new ImagickPixel(), 180);
				break;
			case 6:
				$laImg->rotateimage(new ImagickPixel(), 90);
				break;
			case 8:
				$laImg->rotateimage(new ImagickPixel(), -90);
				break;
		}
	}
	if ($conCoordenadas) {
		$arrExif = exif_read_data($tmp);
		if (isset($arrExif['GPSLatitude'])) {
			$lat = gps($arrExif["GPSLatitude"], $arrExif['GPSLatitudeRef']);
			$long = gps($arrExif["GPSLongitude"], $arrExif['GPSLongitudeRef']);
			$retval = '"longitud":"'.$long.'","latitud":"'.$lat.'",';
		}
	}
	if ($quitaMetadatos) $laImg->stripimage();
	$laImg->writeimage($dest);
	$laImg->resizeimage(200, 200, Imagick::FILTER_POINT, 0.8, true);
	$laImg->setimageformat("jpeg");
	$retval .= '"preview":"data:' . $laImg->getimagemimetype() . ';base64,' . base64_encode($laImg->getimageblob()) . '",';
	$laImg->clear();
	$laImg->destroy();
	return $retval;
}
?>