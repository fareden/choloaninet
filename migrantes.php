<?php
session_start();
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/formatos.php");
require_once("./motores/interno/defs.php");
require_once("./motores/interno/MasterCat.class.php");
$catalogo = new Catalogo('migrante', "P", null, "form-control");	//Sirve para crear el arreglo de la tabla principal
//La siguiente línea define un catálogo detalle, asignar el nombre de la tabla y descomentariar para usar
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
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/personalizado.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
		<link rel="stylesheet" href="js/jquery-ui.min.css" />
		<?= $catalogo->getScripts() ?>
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
					contenido += '<td><button onclick="edita(\'' + datos[i]["id"] + '\')" data-toggle="modal" data-target="#agregaCliente">Modificar</button>  <button onclick="borra(\'' + datos[i]["id"] + '\')">Eliminar</button></td></tr>';
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
			<nav id="herramientas" class="navbar navbar-toggleable-md"  style="background-color: rgba(50, 47, 47, 0.73); height:120px;">
				<?= dameMenuApp(); ?>
			</nav>
		</header>
		<!-- Intro Section -->
		<section class="intro" style="background-image: url(img/fondo2.jpg); min-height: 60%;"></section>
		</section>
		<section style="position: absolute; z-index:1; min-width:100%;">
			<div class="container" style=" margin-top:30px;">
				<div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="row rounded fondo-gris1">
							<div class=" rounded" style="width: 100%;background-color: rgba(50, 47, 47, 0.73); margin:8px;">
								<div class="col-md-12 col-sm-12" style="margin-top:40px;">
									<h3 style="color:#eaa704;">Blog y noticias</h3>
									<input type="text" id="txtBusca" class="col-md-8 form-control" placeholder=" Buscar ">
									<div class="col-md-2">
										<span class="input-group-btn">
											<button class="btn btn-default"  onclick="busca()" id="btnBusca" type="button"> <i class="material-icons align-middle">search</i> </button>
										</span>
									</div>
									<div class="col-md-2">
										<!-- <a href="#"  id="nuevo" class="btn btn-success pull-right" data-toggle="modal" data-target="#agregaCliente" role="button"> <i class="material-icons align-middle">add_circle</i> Agregar</a> -->
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="fondo-gris1 rounded">
								<div class="oferta rounded">
									<H2 class="mb-4">Migrantes registrados</H2>
									<div class="well well-lg color-well table-responsive">
										<div id="vista"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<footer class="text-center fixed-bottom" style="background-color: rgba(50, 47, 47, 0.73); height: 30px;">
			<p>©Datametrix 2018</p>
		</footer>
		<!--modal para agregar almacenamientos-->
		<div id="agregaCliente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="agregaClienteLabel" aria-hidden="true">
			<div class="modal-dialog fondo"> 
				<!--Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Entrada al blog</h5>
						<button type="button" class="close limpia" data-dismiss="modal">&times;</button>
					</div>
					<div class="modal-body">
						<div id="mensaje"></div>
						<div id="forma">
							<form id="frmAlta">
								<table border="0">	
									<?= $catalogo->comoTabla(); ?>
								</table>
								<!-- Cambiar el siguiente campo para cambiar la página de regreso -->
								<input type="hidden" name="<?= ofusca('blog[idusuario]'); ?>" value="<?= $_SESSION['Usuario']['id'] ?>" />
								<input type="hidden" name="r" value="blog.php"/>
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