<?php
session_start();
ini_set("display_errors", 1);
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/MasterCat.class.php");
require_once("./motores/interno/formatos.php");
$catalogo = new Catalogo('usuario', "P", null);    //Sirve para crear el arreglo de la tabla principal
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Catálogo de usuarios</title>
		<link rel="stylesheet" href="js/jquery-ui.min.css" />
		
		<!--CSS-->
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/personalizado.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>	

		<script src="js/jquery.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<?= $catalogo->getScripts() ?>
		<script type="text/javascript">
			//Scripts locales...
			function validapwd() {
				txtPwd = document.getElementById('p1');
				txtPwd2 = document.getElementById('p2');
				ctrlPwd = document.getElementById('elpass');
				var color = '';
				if (txtPwd2.value == '') {
					//Aún no se escribe nada en el segundo campo... pasamos
					ctrlPwd.value = '';
				}else if (txtPwd.value != txtPwd2.value) {
					color = '#fe6e6e';
					ctrlPwd.value = '';
				} else {
					color = '#9ffec2';
					ctrlPwd.value = txtPwd.value;
				}
				txtPwd.style.backgroundColor = color;
				txtPwd2.style.backgroundColor = color;
			}
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
							if (llave != "id") contenido += '<td onclick="edita(\'' + datos[i]["id"] + '\')" data-toggle="modal" data-target="#agregaUsuario">' + datos[i][llave] + '</td>';	
						}
					}
					contenido += '<td><button class="btn btn-danger" onclick="borra(\'' + datos[i]["id"] + '\')" data-toggle="tooltip" data-placement="right" title="Eliminar"><i class="material-icons">delete_forever</i></button></td></tr>';
				}
				contenido = '<table class="table table-striped">' + hdr + '<th></th></tr>' + contenido + '</table>';
				$("#vista").html(contenido);
			}
			
			function errorDatos() {
				console.log("Hubo bronquitas con los datos");
			}
			function inicia() {
				busca();
			}
			function agregaRegistro() {
				//console.log($("#frmAlta").serialize());
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
				return false;
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
							<button class="btn btn-default"  onclick="busca()" id="btnBusca" type="button"> <i class="material-icons align-middle">search</i> </button>
						</span>
					</div>
				</div>
				<div class="col-md-2">
					<div class="input-group">
						<a href="#"  id="nuevo" class="btn btn-success pull-right" data-toggle="modal" data-target="#agregaUsuario" role="button"> <i class="material-icons align-middle">add_circle</i> Agregar</a>
					</div>
				</div>
			</div>
			<div class="row mt-3 col-md-12">
				<div class="well well-lg color-well table-responsive">
					<div id="vista"></div>
				</div>
			</div>
		</div>
		<!--modal para agregar Usuario-->
		<div id="agregaUsuario" class="modal fade"  tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog fondo">
				<!--Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Datos de Usuario</h5>
						<button type="button" class="close limpia" data-dismiss="modal">&times;</button>
					</div>
					<div class="modal-body">
						<div id="mensaje" ></div>
						<div id="forma">
							<form id="frmAlta">
								<table border="0">
									<?= $catalogo->comoTabla(); ?>
									<tr>
										<td><label for="p1">Contrase&ntilde;a:</label></td>
										<td>
											<input type="hidden" name="<?= ofusca("usuario[passwd]"); ?>" id="elpass" value=""/>
											<input type="password" id="p1" value="" onchange="validapwd()" />
										</td>
									</tr>
									<tr>
										<td><label for="p2">Confirmar</label></td>
										<td>
											<input type="password" id="p2" value="" onchange="validapwd()" />
										</td>
									</tr>
								</table>
								<input type="hidden" name="r" value="usuarios.php"/>
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
