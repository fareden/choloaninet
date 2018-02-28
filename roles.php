<?php
session_start();
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/MasterCat.class.php");
require_once("./motores/interno/formatos.php");
$catalogo = new Catalogo('rol', "P", null);    //Sirve para crear el arreglo de la tabla principal
$paginas = new Catalogo("pagina_rol", "D", $catalogo->getConexion());
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Roles de acceso</title>
		<link rel="stylesheet" href="js/jquery-ui.min.css" />
		<!--CSS-->
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/personalizado.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>	

		<script src="js/jquery.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<?= $catalogo->getScripts(); ?>
		<?= $paginas->getScripts(); ?>
		<script type="text/javascript">
			function dibuja(datos) {
				//console.log($("#frmAlta").serialize());
				$("#vista").html("");
				var contenido = "";
				var hdr = '<tr>';
				for (var i = 0; i < datos.length; i++) {
					contenido += '<tr>';
					for (var llave in datos[i]) {
						if (datos[i].hasOwnProperty(llave)) {
							if (i == 0) {
								//Ponemos los encabezados
								if (llave != "id") hdr += '<th>' + llave + '</th>';
							}
							if (llave != "id") contenido += '<td>' + datos[i][llave] + '</td>';
						}
					}
					contenido += '<td><button onclick="edita(\'' + datos[i]["id"] + '\')" data-toggle="modal" data-target="#agregaRol">Modificar</button>  <button class="btn btn-danger" onclick="borra(\'' + datos[i]["id"] + '\')" data-toggle="tooltip" data-placement="right" title="Eliminar"><i class="material-icons">delete_forever</i></button></td></tr>';
				}
				contenido = '<table class="table table-striped">' + hdr + '<th></th></tr>' + contenido + '</table>';
				$("#vista").html(contenido);
			}
			function dibujaDetalle(obj) {
				console.log(obj);
				var fila = '<tr>';
				for (var prop in obj) {
					if (obj[prop] != '-1') fila += '<td>' + obj[prop] + '</td>';
				}
				fila += '<td><p class="btn btn-danger btn-round" role="button" onclick="quitaFila(this)"> - </p></td></tr>';
				$("#tblpagina_rol").append(fila);
			}
			function editaDetalle(id){
				editaDetalle_<?= $paginas->getPrefijo(); ?>(id);
			}

            function quitaFila(i){
				quitadetalle_<?= $paginas->getPrefijo(); ?>("tblpagina_rol",i);
            }
            
			function errorDatos() {
				console.log("Hubo bronquitas con los datos");
			}
		
			function inicia() {
				busca();
				var paginas = '<option value="-1">-- Seleccione uno --</option><?php
					$archs = glob('*.php');
					foreach ($archs as $ar) {
						echo("<option>".$ar. "</option>");
					}
				?>';
				document.getElementById("eNorSEzPzEuML8rPiS8AMwE+fAbs").innerHTML = paginas;
			}
			function agregaRegistro() {
				event.preventDefault();
				console.log($("#frmAlta").serialize());
				$.ajax({
					url: "motores/hanumat.php",
					type: "POST",
					dataType: "JSON",
					data: $("#frmAlta").serialize()
				}).done(function (r) {
					if (r.error == '0') {                        
						$("#mensaje").html("<div class='alert alert-success'> Información guardada correctamente </div>");
						setTimeout(function(){
							window.location.href = r.destino;
						}, 3000);
					}else if (r.error == '2') {
						$("#mensaje").html("<div class='alert alert-danger'> Error al guardar la información</div>");
						setTimeout(function(){
							$("#mensaje").html('');
						}, 3000);
                    }
				});	
			}
			function doLogout() {
				$.ajax({
					url : "motores/hanumat.php",
					type : "POST",
					dataType : "JSON",
					data : {r: 's'}
				}).done(function(r) {
					if (r.error == '0') {
						window.location.reload();
					}
				});
				return false;
			}
		</script>
		<script type="text/javascript">
			$(document).ready( function () {
				$(".limpia" ).click(function(e) {
					$("#frmAlta")[0].reset();
				});
			});
		</script>
	</head>
	<body onload="inicia()">
		<header>
			<nav id="herramientas" class="navbar navbar-toggleable-md navbar-light bg-faded">
				<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#opciones" aria-controls="opciones" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<a class="navbar-brand" href="#"><img src="img/logo-small.png" alt="logo"></a>
				<div id="opciones" class="collapse navbar-collapse">
					<?= dameMenuApp(); ?>
				</div>
			</nav>
		</header>
		<div class="container mt-5">
			<div class="row" >
				<div class="col-md-10">
					<div class="input-group">
						<input type="text" id="txtBusca" class="form-control" placeholder=" Buscar ">
						<span class="input-group-btn">
							<button class="btn btn-default"  onclick="busca()" id="btnBusca" type="button"> <i class="material-icons align-middle">search</i></button>
						</span>
					</div>
				</div>
				<div class="col-md-2">
					<div class="input-group">
						<a href="#"  id="nuevo" class="btn btn-success pull-right" data-toggle="modal" data-target="#agregaRol" role="button"> <i class="material-icons align-middle">add_circle</i> Agregar</a>
					</div>
				</div>
			</div>
			<div class="row mt-3 col-md-12">
				<div class="well well-lg color-well table-responsive">
					<div id="vista"></div>
				</div>
			</div>
		</div>
		<!--modal para agregar rol-->
		<div id="agregaRol" class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog fondo">
				<!--Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Datos de rol</h5>
						<button type="button" class="close limpia" data-dismiss="modal">&times;</button>
						</div>
					<div class="modal-body">
						<div id ="mensaje"></div>
						<div id="forma">
							<form id="frmAlta">
								<table border="0">
									<?= $catalogo->comoTabla(); ?>
									<tr>
										<td><h4>Acceso a páginas</h4></td>
									</tr>
									<?= $paginas->comoTabla(); ?>
								</table>
								<input type="hidden" name="r" value="roles.php"/>
								<input type="hidden" name="ae" value="" id="ae" />
							</form>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default limpia" data-dismiss="modal">Cancelar</button>
						<a href="javascript:void(0)" class="btn btn-primary" onclick="agregaRegistro()">Agregar</a>
					</div>
				</div>
			</div>
		</div>
        <script src="js/popper.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/plugins/jquery.magnific-popup.min.js"></script>
    </body>
</html>
