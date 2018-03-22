<?php
require_once('funciones.php');
//Hay que tener cuidado con esta ruta... 
require_once('motores/interno/conexion.php');
class MuestraCatalogo {
    /****
     * MUESTRACAT
     * La versión de solo-lectura para mastercat.
     *
     * Toma una tabla y genera la estructura de etiquetas necesaria.
     * También genera el método que hace la consulta por AJAX.
     *
     */
    //Propiedades privadas.
    private $dbcon = null;
    private $tabla = "";
    private $modo = "P";
    private $scripts = "";
    private $llave = "";
    private $campos = array();
    private $hayBinario = FALSE;
    private $claseDefault = "";
    private $prefijoFuncs = "";
    private $editables = "";
    
    public function __construct($tabla, $modo, $db = null, $claseDef = "") {
        //Genera una nueva instancia del catálogo, también de la base de datos si no está conectada
        if ($db == null && $this->dbcon == null) {
            $this->dbcon = conectaDB();
        } else {
            $this->dbcon = $db;
        }
        $this->tabla = $tabla;
        $this->modo = $modo;
        $this->claseDefault = $claseDef;
        $this->genera();
        $this->prefijoFuncs = substr(str_shuffle($tabla), 0, 5);
    }
    
    public function getCampos() {
        return $this->campos;
    }
    public function getPrefijo() {
        return $this->prefijoFuncs;
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
            $retval = '<table id="tbl' . $this->tabla . '" class="tabladetalle">';
            $enca = "<tr>";
            $ctrls = "<tr>";
            foreach ($this->campos as $campo) {
                $enca .= "<th>{$campo->getEtiqueta()}</th>";
                $ctrls .= "<td style='display: none;'>{$campo->getControl()}</td>";
            }
            $enca .= "</tr>";
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
        return $retval;
    }
    
    private function generaScriptsDetalle() {
        //Aquí genera la onda de agregar los detalles...
        //Antes era una tabla, pero creo que ahora lo anexaré a un objeto, y luego lo dibujo a tabla.
        //Se debe anexar un prefijo...
        $retval = '<script type="text/javascript">
        function muestraDetalle_'.$this->prefijoFuncs.'(id) {
            //Traemos los datos del detalle...
            var datos = {t: "' . ofusca($this->tabla) . '",l: "' . ofusca(substr($this->editables, 0, -1)) . '", c: "' . ofusca($this->llave) . '|" + id, r: "c"};
            $.ajax({
                url: "./motores/hanumat.php",
                type: "POST",
                dataType: "JSON",
                data: datos
            }).done(function (r) {
                if (r.error == "0") {
                    //Recibimos un listado aquí... limpiamos antes...
                    limpiaDetalle_'.$this->prefijoFuncs.'();
                    for (var i = 0; i < r.registros.length; i++) {';
                        $arr = explode(",", substr($this->editables, 0, -1));
                        foreach($arr as $val) {
                            $retval .= 'if (r.registros[i].hasOwnProperty("'.$val.'")) {asignaValM("'.ofusca($this->tabla . "_" . $val).'", r.registros[i].'.$val.');}';
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
                var objDatos = [];
                var fila = {};';
        foreach ($this->campos as $campo) {
            $hCampo = ofusca($this->tabla . '_' . $campo->getID());
            $tCampo = ofusca($campo->getID());
            $retval .= "fila['$hCampo'] = dameValObj_".$this->prefijoFuncs."(document.getElementById('$hCampo'));";
        }
        $lasLlaves = explode(",", $this->llave);
        foreach($lasLlaves as $l) {
            $tCampo = ofusca($l);
        }
        $retval .= 'objDatos.push(fila); dibujaDetalle(fila, "tbl' . $this->tabla . '");
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
            } else if (obj.type == "checkbox" || obj.type == "radio") {
                obj.checked = false;
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
        function buscaMuestra(pag) {
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
                    l: "'.ofusca($this->llave).'"
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

        function muestra(id) {
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
                            asignaValM(campo, r[campo]);
                        }
                    }
                    if (typeof muestraDetalle === "function") {
                        muestraDetalle(id);
                    }
                } else {
                    console.log(r.errmsg);
                }
            });
        }

        function asignaValM(campo, cont) {
            var c = document.getElementById(campo);
            if (!(c == null || c == undefined)) {
                if (c.tagName == "IMG") {
                    c.src = cont;
                    c.style.display = "block";
                } else if (c.tagName == "TEXTAREA" && tinyMCE != undefined) {
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
        </script>';
        
        return $retval;
    }
    
    private function genera() {
        $qry = "show full columns in " . $this->tabla . ";";
        $rs = $this->dbcon->query($qry);
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
        $idcampo = ofusca($this->tabla . '_' . $nombre);
        $jq_campo = '';
        $filaEstilo = '';
        $lasOpts = "";
        $strCampo = "";
        if (strpos($tipcampo, 'blob') === false) {
            $strCampo = '<input id="' . $idcampo . '" ';
            for ($i = 1; $i < count($opciones); $i++) {
                $elval = substr($opciones[$i], 2);
                switch (substr($opciones[$i], 0, 1)) {
                    case 'C':
                        $strCampo = str_replace('input id="' . $idcampo, 'select id="' . $idcampo, $strCampo) . ' disabled ><option>--Seleccione uno--</option>**F****S**';
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
                            $strCampo = str_replace('input id="' . $idcampo, '**T**textarea disabled id="' . $idcampo, $strCampo);
                        } else if ($elval == 'select') {
                            $strCampo = str_replace('input id="' . $idcampo, 'select id="' . $idcampo, $strCampo) . '"><option>--Seleccione uno--</option>**F****S**';
                        } else {
                            $strCampo .= 'type="' . $elval . '" ';
                        }
                        $strCampo .= '**F**';
                        break;
                    case 'H':
                        $filaEstilo .= 'height: ' . $elval . '; ';
                        break;
                    case 'W':
                        $filaEstilo .= 'width: ' . $elval . '; ';
                        break;
                    case 'S':
                        $strCampo .= 'class="'. $elval . '" ';
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
                $strCampo = str_replace('input id="' . $idcampo, '**T**textarea disabled id="' . $idcampo, $strCampo);
            } else if (substr($tipcampo, 0, 4) == 'enum') {
                $strCampo = str_replace('input id="' . $idcampo, 'select id="' . $idcampo, $strCampo) . ' disabled "><option>--Seleccione uno--</option>**S**';
                preg_match("/^enum\(\'(.*)\'\)$/", $tipcampo, $matches);
                $enum = explode("','", $matches[1]);
                foreach ($enum as $value) {
                    $strCampo .= '<option value="' . $value . '">' . $value . '</option>';
                }
            } else if (strpos($tipcampo, 'blob') !== false) {
                $nommime = ofusca($this->tabla . '[' . $nombre . '_mime]');
                $idmime = ofusca($this->tabla . '_' . $nombre . '_mime');
                $strCampo .= 'type="file" style="position: absolute; left: 0px; top: 0px; height: 1px; width: 1px; opacity: 0; z-index: 1;" onchange="nvoArch_'.$this->prefijoFuncs.'(this, \''.($this->modo == 'P' ? $nomcampo : $idcampo).'\', \''.$lasOpts.'\', \''.($this->modo == 'P' ? $nommime : $idmime).'\')"';
                $this->hayBinario = true;
            }
        }
        if ($this->claseDefault != "" && strpos($strCampo, "class=") === FALSE) {
            $strCampo .= 'class="'.$this->claseDefault.'" ';
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
            $strCampo .= '</select>';
        } else if (strpos($strCampo, 'type="file"') !== false) {
            $strCampo .= ' /><label for="tmp'.$idcampo.'">Agregar</label><div id="vprevias"><img id="'.$idcampo.'" style="max-heigth: 500px; display: none;" /></div>';
        } else {
            $strCampo .= ' disabled />';
        }
        $retval = new CampoMuestra($nombre, $strCampo . $jq_campo, $opciones[0], $this->tabla);
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

class CampoMuestra {
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
