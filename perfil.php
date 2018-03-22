<?php
session_start();
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/formatos.php");
require_once("./motores/interno/defs.php");
require_once("./motores/interno/MasterCat.class.php");
$catalogo = new Catalogo('migrante', "P", null, "form-control");	//Sirve para crear el arreglo de la tabla principal
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
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?&libraries=places&language=es&key=AIzaSyBWe3yPvnEk__zqa04SH0UlDqsjGG_TBWs"></script>
		<?= $catalogo->getScripts(); ?>
		<script type="text/javascript">
			function agregaRegistro() {
				$.ajax({
					url: "motores/hanumat.php",
					type: "POST",
					dataType: "JSON",
					data: $("#frmAlta").serialize()
				}).done(function (r) {
					if (r.error == '0') {
						window.location.href = r.destino;
					}else {
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
					data : {
						r : 's'
					}
				}).done(function(r) {
					if (r.error == '0') {
						window.location.reload();
					}
				});
				//limpiaDers();
				return false;
			}
			function inicia() {
				edita(<?= $_SESSION['Usuario']['id'] ?>);
				preparaMapa();
			}
			function preparaMapa() {
				creaMapa_<?= $catalogo->getPrefijo() ?>();
				creaPunto_<?= $catalogo->getPrefijo() ?>();
			}
		</script>
	</head>
	<body onload="inicia()">
		<header>
			<nav id="herramientas" class="navbar navbar-toggleable-md"  style="background-color: rgba(50, 47, 47, 0.73); height:120px;">
				<?= dameMenuMigrante(); ?>
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
									<h3 style="color:#eaa704;">Perfil de usuario</h3>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="fondo-gris1 rounded col-md-12">
								<div class="oferta rounded">
									<form id="frmAlta" style="padding: 12px;">
										<table>
											<?= $catalogo->comoTabla(); ?>
										</table>
										<input type="hidden" id="ae" name="ae"  />
										<input type="hidden" id="r" name="r" value="landing.php" />
										<a href="#" onclick="agregaRegistro()" class="btn btn-success">¡Guardar cambios!</a>
										<a href="baja.php" class="btn btn-error">Borrar mi cuenta</a>
									</form>
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
		<script src="js/popper.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/plugins/jquery.magnific-popup.min.js"></script>
	</body>
</html>
