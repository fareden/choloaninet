<?php
session_start();
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/formatos.php");
require_once("./motores/interno/defs.php");
require_once("./motores/interno/MasterCat.class.php");
$catalogo = new Catalogo('movimiento', "P", null);	//Sirve para crear el arreglo de la tabla principal
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
		<script type="text/javascript">
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
		</script>
	</head>
	<body>
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
		<div class="container">
			<div class="row" >
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
		<script src="js/popper.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/plugins/jquery.magnific-popup.min.js"></script>
	</body>
</html>
