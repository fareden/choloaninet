<?php
session_start();
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/formatos.php");
require_once("./motores/interno/defs.php");
require_once("./motores/interno/MasterCat.class.php");
$oferta = new Catalogo("oferta", 'P', null, 'form-control');
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?= NOMBRE_APLICACION ?> -- Bienvenido, <?= $_SESSION['Usuario']['nombre']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<meta charset="utf-8">
		<!-- SEO Meta Tags -->
		<meta name="description" content="Descripción" />
		<meta name="keywords" content="Palabras Clave" />
		<meta name="author" content="CarpathiaLab" />

		<!-- Mobile Specific Meta Tag -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<!-- Favicon Icon -->
		<script src="js/jquery.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
		<link rel="icon" href="favicon.ico" type="image/x-icon">
		<!--CSS-->
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/personalizado.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?&libraries=places&language=es&key=AIzaSyBWe3yPvnEk__zqa04SH0UlDqsjGG_TBWs"></script>
		<?= $oferta->getScripts(); ?>
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
								if (llave.substr(0, 2) != "id") hdr += '<th>' + llave + '</th>';
							}
							if (llave.substr(0, 2) != "id") contenido += '<td>' + datos[i][llave] + '</td>';
						}
					}
					contenido += '<td><button onclick="verAlcance(\'' + datos[i]["id"] + '\')" data-toggle="modal" data-target="#verActividad">Ver respuesta</button>  <button onclick="edita(\'' + datos[i]["id"] + '\')" data-toggle="modal" data-target="#agregaCliente">Modificar</button>  <button onclick="borra(\'' + datos[i]["id"] + '\')">Eliminar</button></td></tr>';
				}
				if (datos.length > 2) {
					$("#btnAgregar").hide();
					$("#btnAgregar").removeAttr("data-target");
					$("#btnAgregar").removeAttr("data-toggle");
				}
				contenido = '<table class="table table-striped">' + hdr + '</tr>' + contenido + '</table>';
				$("#vista").html(contenido);
			}
			function errorDatos() {
				console.log("Hubo bronquitas con los datos");
			}
			function inicia() {
				busca();
			}
			function preparaMapa() {
				creaMapa_<?= $oferta->getPrefijo() ?>();
				creaPunto_<?= $oferta->getPrefijo() ?>();
			}
			function agregaRegistro() {
				$.ajax({
					url: "motores/hanumat.php",
					type: "POST",
					dataType: "JSON",
					data: $("#frmAlta").serialize()
				}).done(function (r) {
					if (r.error == '0') {
						$("#mensaje").html("<div class='alert alert-success'> Grabado correctamente </div>");
						setTimeout(function(){
							window.location.href = r.destino;
						
						}, 3000);
					}else if (r.error == '2') {
						$("#mensaje").html("<div class='alert alert-danger'> Error al guardar </div>");
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
				//limpiaDers();
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
			<nav id="herramientas" class="navbar navbar-toggleable-md navbar-light bg-faded"  style="background-color: rgba(105, 100, 100, 0.73); height:120px;">
				<!--
				<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#opciones" aria-controls="opciones" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<a class="navbar-brand" href="#"><img src="img/logo-small.png" alt="logo"></a>
				<div id="opciones" class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li class="nav-item"><a class="nav-link" href="registro.php">REGISTRARSE</a></li>
						<li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#firmado">INICIAR SESIÓN</a></li>
					</ul>
				</div>-->
				<a class="navbar-brand" href="#"><img src="img/logo-small.png" alt="logo"></a>
				<div id="opciones" class="collapse navbar-collapse">
					<?= dameMenuMigrante(); ?>
				</div>
			</nav>
		</header>
		<div class="container">
			<div class="row" >
				<h1>Busca aliados para emprender un negocio</h1>
				<h3>Actualmente haz iniciado los siquientes proyectos</h3>
				<div id="vista"></div>
			</div>
			<div class="row">
				<a href="#" id="btnAgregar" class="btn btn-success" role="button" data-target="#divAgregar" data-toggle="modal" onclick="preparaMapa()">¡Iniciar un proyecto!</a>
			</div>
		</div>
		<!-- Footer -->
		<footer>
			<div class="row">
				<div class="col-lg-12">
					<p>2018 &copy; Datametrix </p>
				</div>
			</div>
		</footer>
		<!-- MODAL -->
		<div id="divAgregar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="agregaClienteLabel" aria-hidden="true">
			<div class="modal-dialog fondo"> 
				<!--Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Oferta de emprendimiento</h5>
						<button type="button" class="close limpia" data-dismiss="modal">&times;</button>
					</div>
					<div class="modal-body">
						<div id="mensaje"></div>
						<div id="forma">
							<form id="frmAlta">
								<table border="0">	
									<?= $oferta->comoTabla(); ?>
								</table>
								<!-- Cambiar el siguiente campo para cambiar la página de regreso -->
								<input type="hidden" name="<?= ofusca('oferta[tipo]') ?>" value="emprendimiento" />
								<input type="hidden" name="<?= ofusca('oferta[idcreador]') ?>" value="<?= $_SESSION['Usuario']['id'] ?>" />
								<input type="hidden" name="r" value="emprende.php"/>
								<input type="hidden" name="ae" value="" id="ae" />
							</form>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="limpia btn btn-default " data-dismiss="modal">Cancelar</button>
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
