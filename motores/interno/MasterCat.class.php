<?php
require_once('funciones.php');
//Hay que tener cuidado con esta ruta... 
require_once('motores/interno/conexion.php');
class Catalogo {
	/****
	 * NUEVA VERSIÓN A OBJETOS DE MASTERCAT
	 * Se cambia a una clase para volverlo más flexible a la hora de usarlo en varias instancias...
	 */
	//Propiedades privadas.
	private $dbcon = null;
	private $tabla = "";
	private $modo = "P";
	private $scripts = "";
	private $llave = "";
	private $editables = "";
	private $campos = array();
	private $avalidar = array();
	private $nomForma = "frmAlta";
	private $hayBinario = FALSE;
	private $elMapa = "";
	private $claseDefault = "";
	private $prefijoFuncs = "";
	
	public function __construct($tabla, $modo, $db = null, $claseDef = "") {
		//Genera una nueva instancia del catálogo, también de la base de datos si no está conectada
		if ($db == null && $this->dbcon == null) {
			$this->dbcon = conectaDB();
		} else {
			$this->dbcon = $db;
		}
		$this->prefijoFuncs = substr(str_shuffle($tabla), 0, 5);
		$this->tabla = $tabla;
		$this->modo = $modo;
		$this->claseDefault = $claseDef;
		$this->genera();
	}
	
	public function getCampos() {
		return $this->campos;
	}
	
	public function setNombreForma($nom) {
		$this->nomForma = $nom;
	}
	
	public function getConexion() {
		return $this->dbcon;
	}
	
	public function getCampo($id) {
		$retval = null;
		foreach ($this->campos as $camp) {
			if ($camp->getID() == $id) {
				$retval = $camp;
				break;
			}
		}
		return $retval;
	}
	public function getPrefijo() {
		return $this->prefijoFuncs;
	}
	public function getLlave() {
		return $this->llave;
	}
	/*
	public function setClaseDefault($clase) {
		$this->claseDefault = $clase;
	}
	*/
	public function comoTabla() {
		//Genera una tabla planita, etiqueta -- control
			//Solamente generamos el antiguo modo de tabla, es una especie de compatibilidad backwards...
		$retval = "";
		if ($this->modo == 'P') {
			//var_dump($arrCampos);
			foreach ($this->campos as $campo) {
				$retval .= '<tr><td>' . $campo->getEtiqueta() . '</td><td>' . $campo->getControl() . '</td></tr>';
			}
		} else if ($this->modo == 'D') {
			$retval = '<table id="tbl' . $this->tabla . '" class="table table-striped">';
			$enca = "<tr>";
			$ctrls = "<tr onkeypress='agregaFila_".$this->prefijoFuncs."(event)'>";
			foreach ($this->campos as $campo) {
				$enca .= "<th>{$campo->getEtiqueta()}</th>";
				$ctrls .= "<td>{$campo->getControl()}</td>";
			}
			$enca .= "<td><a id='btnAddDetalle' href='javascript:void(0)' type='button' class='btn btn-success' onclick='agregaFila_".$this->prefijoFuncs."(null)'> + </a></td></td></tr>";
			$ctrls .= "</tr>";
			$retval .= $enca . $ctrls . '</table>';
			 
		}
		return $retval;
	}
	
	public function conFormato($formato, $remover = TRUE) {
		//Necesitamos definir el formato, básicamente necesito dos campos a sustituir:
		//1. El lugar donde voy a poner la etiqueta
		//2. El punto donde voy a insertar el contro.
		//3. OPCIONAL: poner la clase de tamaños personalizada que viene del control...
		//Necesitamos un marcador, creo que será **ETIQUETA**,  y **CLASE** opcionalmente
		$retval = "";
		foreach ($this->campos as $control) {
			$paso = str_replace("**ETIQUETA**", $control->getEtiqueta(), $formato);
			$paso = str_replace("**CONTROL**", $control->getControl(), $paso);
			if (strpos($paso, "**CLASE**")) {
				$paso = str_replace("**CLASE**", $control->extraeClase($remover), $paso);
			}
			$retval .= $paso;
		}
		return $retval;
	}
	
	public function setReglasImagen($extraeCoords = false, $redimensiona = 0, $quitaMetadatos = true) {
		//Establece las reglas predeterminadas para este catálogo.
		
	}
	
	private function generaMapas() {
		$retval = "<script type='text/javascript'>
		var origen = new google.maps.LatLng(20.40, -104.20);
		var marcador = null;
		var map = null;
		function creaMapa_".$this->prefijoFuncs."() {
			var myOptions = {
				zoom: 17,
				center: origen,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById('mapa_{$this->hayMapa}'), myOptions);
		}
		function creaPunto_".$this->prefijoFuncs."() {
			if (marcador == null) {
				var strMarca = 'Seleccione el punto en el mapa';
				var pos = map.getCenter();
				marcador = new google.maps.Marker({
					position: pos, 
					draggable: true,
					animation: google.maps.Animation.DROP, 
					map: map, 
					title: strMarca,
					scale: 8
				});
				google.maps.event.addListener(marcador, 'dragend', function(event){enviaPunto_".$this->prefijoFuncs."(event.latLng);});
			}
		}
		function enviaPunto_".$this->prefijoFuncs."(pos) {
			document.getElementById('{$this->hayMapa}').value = 'geo||pu'+pos.lng()+' '+pos.lat();
		}
		</script>";
		return $retval;
	}
	
	private function generaSubidor() {
		//Tenemos una bronca, esto debe ser diferente entre detalle y campo... por lo pronto bloquearé a 1
		$retval = "<script type='text/javascript'>
		function nvoArch_".$this->prefijoFuncs."(obj, elCampo, opts = '', elMime) {
			for (var i = 0; i < obj.files.length; i++) {
				subeBinario_".$this->prefijoFuncs."(obj.files[i], elCampo, obj, opts, elMime);
			}
		}
		function subeBinario_".$this->prefijoFuncs."(blob, elCampo, obj, opts, elMime) {
			var fd = new FormData();
			fd.append('archivo', blob);
			fd.append('r', 'f');
			//Aquí van las demás opciones: cp para traer el punto, re para poner nuevo tamaño de imagen, sm para quitar metadatos
			if (opts != '') {
				var lO = opts.split(',');
				for(var i = 0; i < lO.length; i++) {
					var eO = lO[i].split(':');
					if (eO.length == 2) fd.append(eO[0],eO[1]);
				}
			}
			$.ajax({
				url: 'motores/hanumat.php',
				type: 'POST',
				data: fd,
				processData: false,
				dataType: 'JSON',
				contentType: false
			}).done(function (r) {
				if (r.error == '0') {
					$('#vprevias').html('<img src=\"' + r.preview + '\" style=\"max-width: 250px; max-height: 250px;\"/>');\n";
			if ($this->modo == 'P') {
				$retval .= "$('#{$this->nomForma}').append('<input type=\"hidden\" name=\"'+elCampo+'\" value=\"'+r.archivo+'\" />');";
				$retval .= "$('#{$this->nomForma}').append('<input type=\"hidden\" name=\"'+elMime+'\" value=\"'+r.mime+'\" />');";
			} else {
				$retval .= "document.getElementById(elCampo).value = r.archivo;\n";
				$retval .= "document.getElementById(elMime).value = r.mime;\n";
			}
				
			$retval .= "obj.disabled = true;
				}
			});
		}
		</script>";
		return $retval;
	}
	public function getScripts() {
		$retval = "";
		switch ($this->modo) {
			case 'P':
				$retval = $this->generaScriptsPrincipal();
				break;
			case 'D':
				$retval = $this->generaScriptsDetalle();
				break;
		}
		if ($this->hayBinario) $retval .= $this->generaSubidor();
		if ($this->hayMapa != "") $retval .= $this->generaMapas();
		return $retval;
	}
	
	private function generaScriptsDetalle() {
		//Aquí genera la onda de agregar los detalles...
		//Antes era una tabla, pero creo que ahora lo anexaré a un objeto, y luego lo dibujo a tabla.
		//Se debe anexar un prefijo...
		$retval = '<script type="text/javascript">
		function quitadetalle_'.$this->prefijoFuncs.'(tabla, fila) {
			var tbl = document.getElementById(tabla);
			$($(".dtl")[fila.parentElement.parentElement.rowIndex -2]).remove();
			tbl.deleteRow(fila.parentElement.parentElement.rowIndex);
		}
		
		function editaDetalle_'.$this->prefijoFuncs.'(id) {
			//Traemos los datos del detalle...
			var datos = {t: "' . ofusca($this->tabla) . '",l: "' . ofusca(substr($this->editables, 0, -1)) . '", c: "' . ofusca(str_replace("id,", "", $this->llave)) . '|" + id, r: "c"};
			$.ajax({
				url: "./motores/hanumat.php",
				type: "POST",
				dataType: "JSON",
				data: datos
			}).done(function (r) {
				if (r.error == "0") {
					//Recibimos un listado aquí... limpiamos antes...
					limpiaDetalle_'.$this->prefijoFuncs.'();
					$("#'.$this->nomForma.' .dtl").remove();
					$("#tbl'.$this->tabla.' tr:gt(1)").remove();
					for (var i = 0; i < r.registros.length; i++) {';
						$arr = explode(",", substr($this->editables, 0, -1));
						foreach($arr as $val) {
							if ($val != "") $retval .= 'if (r.registros[i].hasOwnProperty("'.$val.'")) {asignaVal("'.ofusca($this->tabla . "_" . $val).'", r.registros[i].'.$val.');}';
						}
						$retval .= 'agregaFila_'.$this->prefijoFuncs.'(null, id);
					}
				} else {
					console.log(r.errmsg);
				}
			});
		}
		function agregaFila_'.$this->prefijoFuncs.'(e, obj = -1) {
			if (e == null || e.keyCode == 13) {
				if (e != null) e.preventDefault();
				if ((v = $("#ae").val()) != "") obj = v.substr(v.indexOf("|")+1);
				var objDatos = [];
				var fila = {};
				var ctrl = "<input type=\"hidden\" name=\"'.ofusca('__'.$this->tabla).'[]\" value=\"";
				var valido = true;
				var objVal = ""';
		foreach ($this->campos as $campo) {
			$hCampo = ofusca($this->tabla . '_' . $campo->getID());
			$tCampo = ofusca($campo->getID());
			if ($campo->esRequerido()) {
				$retval .= "\nobjVal = dameValObj_".$this->prefijoFuncs."(document.getElementById('$hCampo'));
				if (objVal == '') {
					document.getElementById('$hCampo').style.background = '#FF9FBF';
					valido = false;
				} else {
					valido = (valido && true);
				}";
			}
			$retval .= "\nif (valido) {
				objVal = dameValObj_".$this->prefijoFuncs."(document.getElementById('$hCampo'));
				fila['$hCampo'] = objVal;
				ctrl += '{$tCampo}|' + objVal + '|-|';
			}";
		}
		$lasLlaves = explode(",", $this->llave);
		foreach($lasLlaves as $l) {
			if ($l != "id") {
				$tCampo = ofusca($l);
				$retval .= 'ctrl += "'.$tCampo.'|"+obj+"|-|";';
			}
		}
		$retval .= 'ctrl += \'" class="dtl" />\';if (valido) {
			objDatos.push(fila); dibujaDetalle(fila, "tbl' . $this->tabla . '"); $("#'.$this->nomForma.'").append(ctrl); limpiaDetalle_'.$this->prefijoFuncs.'();
			} else {console.log("Complete los datos faltantes, por favor");}
			}
		}
		function dameValObj_'.$this->prefijoFuncs.'(obj) {
			if (obj.type == "checkbox" || obj.type == "radio") {
				return (obj.checked ? "1": "0");
			} else {
				return obj.value.trim();
			}
		}
		function limpiaObjDetalle_'.$this->prefijoFuncs.'(obj) {
			if(obj.type == "text" || obj.type == "textarea") {
				obj.value = "";
			} else if (obj.type == "number") {
				obj.value = 0;
			} else if (obj.type == "date") {
				obj.value = "";
			} else if (obj.type == "checkbox" || obj.type == "radio") {
				obj.checked = false;
			} else if (obj.tagName == "SELECT") {
				obj.value = -1;
			}
		}
		function limpiaDetalle_'.$this->prefijoFuncs.'() {
			var x = $("#tbl' . $this->tabla . ' input, #tbl' . $this->tabla . ' select");
			for (var i = 0; i < x.length; i++) {
				limpiaObjDetalle_'.$this->prefijoFuncs.'(x[i]);
			}
		}
		
		</script>';
		return $retval;
	}
	
	private function generaScriptsPrincipal() {
		$retval = '<script type="text/javascript">
		function busca(pag, filtro = "") {
			pag = pag || "0";
			$.ajax({
				url: "./motores/hanumat.php",
				type: "POST",
				dataType: "JSON",
				data: {
					t: "'. ofusca($this->tabla) .'",
					r: "b",
					b: $("#txtBusca").val(),
					p: pag,
					l: filtro
				}
			}).done(function (r){
				//Renderear el resultado a la tabla
				if (r.error == "0") {
					dibuja(r.registros);
					$("#paginas").html(r.paginador);
				} else {
					//no hay registros
					errorDatos();
				}
			});
		}
		
		function borra(id) {
			if (confirm("¿Seguro desea borrar este registro?")) {
				var datos = "'.ofusca("2{$this->llave}") . '|"+id+"|'.ofusca($this->tabla).'";
				$.ajax({
					url: "./motores/hanumat.php",
					type: "POST",
					dataType: "JSON",
					data: {ae: datos, r: "n"}
				}).done(function (r) {
					if (r.error == "0") {
						location.reload();
					}
				});
			}
			return false;
		}
		
		function edita(id) {
			limpia();
			$("#ae").val("'.ofusca("1{$this->llave}") . '|"+id);
			var datos = {t: "' . ofusca($this->tabla) . '",l: "' . ofusca(substr($this->editables, 0, -1)) . '", c: "' . ofusca($this->llave) . '|" + id, r: "e"};
			$.ajax({
				url: "./motores/hanumat.php",
				type: "POST",
				dataType: "JSON",
				data: datos
			}).done(function (r) {
				if (r.error == "0") {
					for (var campo in r) {
						if (r.hasOwnProperty(campo) && campo != "error") {
							asignaVal(campo, r[campo]);
						}
					}
					if (typeof editaDetalle === "function") {
						editaDetalle(id);
					}
				} else {
					console.log(r.errmsg);
				}
			});
		}

		function asignaVal(campo, cont) {
			var c = document.getElementById(campo);
			if (!(c == null || c == undefined)) {
				if (c.tagName == "IMG") {
					c.src = cont;
					//c.style.display = "block";
					$(c).show();
				} else if (c.tagName == "TEXTAREA" && tinyMCE != undefined && cont != null) {
					tinyMCE.get(campo).setContent(cont, {format: "html"});
				} else if (!(c.tagName == "SELECT" || c.tagName == "INPUT")) {
					c.innerHTML = cont;
				} else if (c.tagName == "INPUT" && (c.type == "checkbox" || c.type == "radio")) {
					c.checked = (cont == "1"? true : false);
				} else {
					c.value = cont;
				}
			}
		}
		
		function valida() {
			var valido = true;
			var obj = undefined;';
		foreach ($this->campos as $campo) {
			if ($campo->esRequerido()) {
				$hCampo = ofusca($this->tabla . '_' . $campo->getID());
				$retval .= "obj = document.getElementById('{$hCampo}');
				if (obj.value == '') {
					obj.style.background = '#FF9FBF';
					valido = false;
				}";
			}
		}
		$retval .= "if (!valido) {
					console.log('complete los datos faltantes, por favor');
				}
				return valido;
			}
			
			function limpia() {
				document.getElementById('{$this->nomForma}').reset();
				$('#ae').val('');
				if (typeof limpiaDetalle === 'function') limpiaDetalle();
			}
		</script>";
		
		return $retval;
	}
	
	private function genera() {
		$qry = "show full columns in " . $this->tabla . ";";
		$rs = $this->dbcon->query($qry);
		$jquery = '';
		$elcampo = array();
		if ($rs) {
			while ($fila = $rs->fetch_row()) {
				if (!($fila[4] == 'PRI' && $fila[6] == 'auto_increment' || $fila[8] == '')) {
					$this->editables .= $fila[0] . ',';
					$tipcampo = $fila[1];
					//No estamos usando el tipocampo, hay que parsearlo...
					$optcampo = explode('|', $fila[8]);
					$nvoCampo = $this->creaCampo($fila[0], $optcampo, $tipcampo, ($fila[3] == 'NO' ? TRUE : FALSE));
					array_push($this->campos, $nvoCampo);
					/*
					if ($fila[3] == 'NO') {
						$nvoCampo->setRequerido(TRUE);
					}
					*/
				} else if ($fila[4] == 'PRI') {
					//Vamos a generar un campo hidden para poner el ID de edición...
					$this->llave .= $fila[0] . ",";
				}
			}
			$this->llave = substr($this->llave, 0, -1);
			$rs->free();
		}
	}

	private function creaCampo($nombre, $opciones, $tipcampo, $requerido) {
		$nomcampo = ofusca($this->tabla . '[' . $nombre . ']');
		$idcampo = ofusca($this->tabla . '_' . $nombre);
		$jq_campo = '';
		$filaEstilo = '';
		$lasOpts = "";
		if (strpos($tipcampo, 'blob') === false) {
			$strCampo = '<input id="' . $idcampo . '"' . ($requerido && $this->modo == 'P' ? " required " : " ");
			if ($this->modo == 'P') $strCampo .= 'name="' . $nomcampo.'"';
			for ($i = 1; $i < count($opciones); $i++) {
				$elval = substr($opciones[$i], 2);
				switch (substr($opciones[$i], 0, 1)) {
					case 'C':
						$strCampo = str_replace('input id="' . $idcampo, 'select id="' . $idcampo, $strCampo) . ' **SO** ><option value="-1">-- Seleccione uno --</option>**F****S**';
						$opts = explode(',', $elval);
						//Tenemos que poner la tabla...
						if (count($opts) == 2) {
							$qryOpts = "select id, $opts[1] from $opts[0] order by $opts[1];";
						} else {
							$qryOpts = "select $opts[1], $opts[2] from $opts[0] order by $opts[2];";
						}
						
						if ($result = $this->dbcon->query($qryOpts)) {
							while ($row = $result->fetch_row()) {
								$strCampo .= '<option value="' . $row[0] . '">' . $row[1] . '</option>';
							}
							$result->free();
						} else {
							error_log("No pudo traer tabla externa con: $qryOpts");
						}
						break;
					case 'T':
						if ($elval == 'textarea') {
							$strCampo = str_replace('input id="' . $idcampo, '**T**textarea id="' . $idcampo, $strCampo);
						} else if ($elval == 'select') {
							$strCampo = str_replace('input id="' . $idcampo, 'select id="' . $idcampo, $strCampo) . ' **SO** ><option>--Seleccione uno--</option>**F****S**';
						} else if ($elval == 'radio') {
							$strCampo = str_replace($idcampo, $idcampo . "_1", $strCampo) . 'type="radio" **R**';
						} else {
							$strCampo .= 'type="' . $elval . '" ';
						}
						$strCampo .= '**F**';
						break;
					case 'D':
						$strCampo .= 'value = "' . $elval . '" ';
						break;
					case 'O':
						$strCampo .= 'onclick="' . $elval . '" ';
						break;
					case 'M':
						$strCampo .= 'onchange="' . $elval . '" ';
						break;
					case 'H':
						$filaEstilo .= $elval;
						break;
					case 'W':
						$filaEstilo .= 'width: ' . $elval . '; ';
						break;
					case 'S':
						$strCampo .= 'class="'. $elval . '" ';
						break;
					case 'J':
						$opts = explode(',', $elval, 2);
						//Función para jQuerys...
						$this->scripts .= creaJQueryAC($idcampo,  $opts[0], $opts[1]);
						$strCampo .= 'type = "text"**F**';
						$jq_campo = '<input type="hidden" id="jq' . $idcampo . '" />';
						break;
					case 'P':
						$strCampo .= 'onkeypress="' . $elval . '" ';
						break;
					case 'A':
						$strCampo .= 'autocomplete="'.$elval.'" ';
						break;
					case 'F':   //Valor máximo
						$strCampo .= 'max="'.$elval.'" ';
						break;
					case 'G':
						$strCampo .= 'min="'.$elval.'" ';
						break;
					case 'I':
						$strCampo .= 'pattern="'.str_replace(" ", "|", $elval).'" ';
						break;
					case 'K':
						$strCampo .= 'placeholder="'.$elval.'" ';
						break;
					case 'L':
						$strCampo .= 'autocomplete="'.$elval.'" ';
						break;
				}
			}
		} else {
			$strCampo = '<input id="tmp'.$idcampo.'" ';
			for ($i = 1; $i < count($opciones); $i++) {
				$elval = substr($opciones[$i], 2);
				switch (substr($opciones[$i], 0, 1)) {
					case 'B':   //MIME aceptados
						$strCampo .= 'accept="'.$elval.'" ';
						break;
					case 'E':   //opciones de imágen
						$lasOpts = $elval;
						break;
				}
			}
		}
		if (strpos($strCampo, '**F**') > 0) {
			$strCampo = str_replace('**F**', '', $strCampo);
		} else {
			if (substr($tipcampo, 0, 3) == 'int' || substr($tipcampo, 0, 7) == 'tinyint') {
				$strCampo .= 'type="number" ';
			} else if (substr($tipcampo, 0, 7) == 'decimal') {
				//$f = str_pad('0.', substr($tipcampo, strpos($tipcampo, ','), -1));
				$f = '0.00';
				$strCampo .= 'type="number" placeholder="'.$f.'"';
			} else if ($tipcampo == 'char(1)') {
				$strCampo .= 'type="checkbox" value="1"';
			} else if (substr($tipcampo, 0, 7) == 'varchar' || substr($tipcampo, 0, 4) == 'char') {
				$strCampo .= 'type="text" maxlength="' . substr($tipcampo, strpos($tipcampo, '(') + 1, -1) . '" ';
			} else if (substr($tipcampo, -4) == 'text') {
				$strCampo = str_replace('input id="' . $idcampo, '**T**textarea id="' . $idcampo, $strCampo);
			} else if (substr($tipcampo, 0, 4) == 'enum') {
				$strCampo = str_replace('input id="' . $idcampo, 'select id="' . $idcampo, $strCampo) . ' **SO**><option>--Seleccione uno--</option>**S**';
				preg_match("/^enum\(\'(.*)\'\)$/", $tipcampo, $matches);
				$enum = explode("','", $matches[1]);
				foreach ($enum as $value) {
					$strCampo .= '<option value="' . $value . '">' . $value . '</option>';
				}
			} else if ($tipcampo == 'datetime') {
				$strCampo .= 'type="datetime-local"';
			} else if ($tipcampo == 'date') {
				$strCampo .= 'type="date"';
			} else if ($tipcampo == 'time') {
				$strCampo .= 'type="time" ';
			} else if (strpos($tipcampo, 'blob') !== false) {
				$nommime = ofusca($this->tabla . '[' . $nombre . '_mime]');
				$idmime = ofusca($this->tabla . '_' . $nombre . '_mime');
				$strCampo .= 'type="file" style="position: absolute; left: 0px; top: 0px; height: 1px; width: 1px; opacity: 0; z-index: 1;" onchange="nvoArch_'.$this->prefijoFuncs.'(this, \''.($this->modo == 'P' ? $nomcampo : $idcampo).'\', \''.$lasOpts.'\', \''.($this->modo == 'P' ? $nommime : $idmime).'\')"';
				$this->hayBinario = true;
			} else if ($tipcampo == 'point') {
				error_log("Hay un dato geográfico");
				$this->hayMapa = $idcampo;
				$strCampo .= 'type="hidden"';
			}
		}
		if ($this->claseDefault != "" && strpos($strCampo, "class=") === FALSE) {
			$strCampo .= ' class="'.$this->claseDefault.'" ';
		}
		if ($filaEstilo != '') {
			$strCampo .= 'style="' . $filaEstilo . '" ';
			$filaEstilo = '';
		}
		if (strpos($strCampo, '**T**')) {
			$strCampo = str_replace('**T**', '', $strCampo);
			$strCampo .= '></textarea>';
		} else if (strpos($strCampo, '**S**')) {
			$strCampo = str_replace('**S**', '', $strCampo);
			$selOpts = substr($strCampo, strrpos($strCampo, ">") + 1);
			$strCampo = str_replace('**SO**', $selOpts, $strCampo);
			$strCampo = substr($strCampo, 0, strrpos($strCampo, ">") +1) . '</select>';
		} else if (strpos($strCampo, 'type="file"') !== false) {
			$strCampo .= ' /><label class="btn btn-normal btn-archivo" role="button" for="tmp'.$idcampo.'">Anexar</label>'.($this->modo == 'D' ? '<input type="hidden" id="'.$idcampo.'" /><input type="hidden" id="'.ofusca($this->tabla . '_' . $nombre.'_mime').'" />' : '').'<div id="vprevias"><img id="'.$idcampo.'" style="max-heigth: 250px; max-width: 250px; display: none;" /></div>';
		} else if ($tipcampo == 'point') {
			$strCampo .= ' /><div id="mapa_'.$idcampo.'" style="width: 600px; height: 400px;"></div>';
		} else if (strpos($strCampo, "**R**")) {
			$strCampo = str_replace('**R**', '', $strCampo);
			if ($tipcampo == "char(1)") {
				$strCampo = '<div class="row">'.$strCampo.'value="1" class="col-md-1"><label for="'.$idcampo.'_1"class="col-md-1">Sí</label>'.str_replace($idcampo . "_1", $idcampo . "_0", $strCampo) .'value="0" checked class="col-md-1"><label for="'.$idcampo.'_0" class="col-md-1">No</label></div>';
			}
			//TODO Tenemos que pensar aquí en las opciones de enumeración.
		} else if (strpos($strCampo, 'checkbox')) {
			$strCampo = '<input type="hidden" value="0" name="'.$nomcampo.'" />'.$strCampo.' />';
		}else {
			$strCampo .= '/>';
		}
		$retval = new Campo($nombre, $strCampo . $jq_campo, $opciones[0], $this->tabla);
		$retval->setRequerido($requerido);
		return $retval;
	}

	private function creaJQueryAC($control, $campos) {
		$tbl = ofusca($this->tabla);
		$cmp = ofusca($campos);
		$retval = "$('#$control').autocomplete({
			source: function(req, response) {
				$.ajax({type: 'POST', url: './motores/hanumat.php', data: {term: req.term, r: 'a', t: $tbl, c: $cmp}, success: response, dataType: 'json'});
			},
			minLength: 3,
			focus: function (event, ui){
				event.preventDefault();
				$(this).val(ui.item.label);
			},
			select: function(event, ui) {
				event.preventDefault();
				$('#jq$control').val(ui.item.value);
			}
		});";
		return $retval;
	}

}

class Campo {
	private $control = "";
	private $etiqueta = "";
	private $validar = "";
	private $id = "";
	private $tabla = "";
	
	function __construct($id, $cont, $etiq, $tbl) {
		$this->id = $id;
		$this->control = $cont;
		$this->etiqueta = $etiq;
		$this->tabla = $tbl;
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getControl() {
		return $this->control;
	}
	
	public function setRequerido($req) {
		$this->validar = $req;
	}
	
	public function esRequerido() {
		return $this->validar;
	}
	
	public function getEtiqueta() {
		return '<label for="'.ofusca($this->tabla . "_" . $this->id).'">'.$this->etiqueta.'</label>';
	}
	
	public function extraeClase($remover = TRUE) {
		//Esta función regresa la clase del control quitándola del mismo.
		$retval = "";
		$pos = strpos("class=", $this->control);
		if ($pos != FALSE) {
			$paso = substr($this->control, $pos + 7);
			$paso = substr($paso, 0, strpos('" '));
			if ($remover) $this->control = str_replace($paso, "", $this->control);
			$retval = $paso;
		}
		return $retval;
	}
}
?>
