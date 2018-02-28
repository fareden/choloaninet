<?php
require_once("./motores/interno/defs.php");
session_start();
$hayUsuario = (isset($_SESSION['Usuario']) && $_SESSION['Usuario']['rol'] != 't');
if (!$hayUsuario) {
	$_SESSION['Usuario'] = array("rol" => "t", "publica" => "invalida");
	?>
<!DOCTYPE html>
<html>
	<head>
		<title><?= NOMBRE_APLICACION ?></title>
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
		<!--CSS-->
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
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
			function firmado() {
				$.ajax({
					url : './motores/hanumat.php',
					type : 'post',
					dataType : 'JSON',
					data : {
						r : 'l',
						usr : $("#usuario").val(),
						pwd : $("#pwd").val()
					}
				}).done(function(r) {
					if (r.error == '0') {
						window.location.href = "./" + r.pagina;
					} else {
						$("#mensaje").html("<div class='alert alert-danger'>"+r.errmsg+" intente nuevamente</div>");
						setTimeout(function(){
							 $("#mensaje").html('');
						}, 4000);
					}
				});
				return false;
			}
		</script>
	</head>
	<body class="fixed-footer">
		<!--Modal (Signin/Signup Page)-->
		<div class="modal fade" id="signin-page">
			<div class="modal-dialog">
				<div class="container">
					<div class="modal-form">
						<div class="tab-content">
							<!-- Sign in form -->
							<form class="tab-pane transition scale fade in active" id="signin-form" autocomplete="off">
								<input type="hidden" value="l" name="r" />
								<center><img src="img/logo-big.png" alt="" height="100%" width="100">
									<h3 class="modal-title">Iniciar Sesión</h3>
								</center>
								<div class="form-group space-top-2x">
									<label for="usuario" class="sr-only">Usuario</label>
									<input type="email" class="form-control" id="usuario" placeholder="Usuario" readonly onfocus="$(this).removeAttr('readonly');" required>
									<span class="error-label"></span>
									<span class="valid-label"></span>
								</div>
								<div class="form-group">
									<label for="password" class="sr-only">Contraseña</label>
									<input type="password" class="form-control" id="pwd" placeholder="Contraseña" readonly onfocus="$(this).removeAttr('readonly');" required>
									<a class="helper-link" href="#">Se te olvidó tu contraseña?</a>
									<span class="error-label"></span>
									<span class="valid-label"></span>
								</div>
								<label class="checkbox">
									<input type="checkbox">
									Recuérdame </label>
								<div class="space-top-2x clearfix">
									<button type="button" class="btn-round btn-ghost btn-danger pull-left" data-dismiss="modal">
										<i class="flaticon-cross37"></i>
									</button>
									<button type="button" value="enviar" onclick="firmado()" class="btn-round btn-ghost btn-success pull-right">
										<i class="flaticon-correct7"></i>
									</button>
								</div>
							</form>
						</div>
						<!-- Hidden real nav tabs -->
						<ul class="nav-tabs hidden">
							<li id="form-2">
								<a href="#signin-form" data-toggle="tab">Entrar</a>
							</li>
						</ul>
					</div>
				</div>
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->
		<!-- Intro Section -->
		<section class="intro" style="background-image: url(img/intro/intro-bg.jpg);">
			<div class="gradient"></div>
			<div class="container">
				<div class="column-wrap">
					<!-- Middle Column-->
					<div class="column c-middle" style="width: 80%; vertical-align: middle;">
						<h1 class="logo"><img src="img/logo-big.png" alt="">CholoaniNET</h1>
					</div>
					<!-- Right Column-->
					<div class="column c-right">
						<!-- Navi -->
						<div class="navi">
							<a href="#" data-toggle="modal" data-target="#signin-page">Iniciar Sesión</a>
						</div>
					</div>
				</div>
			</div>
		</section><!-- Intro Section End -->
		<!-- Footer -->
		<!-- Javascript (jQuery) Libraries and Plugins -->
		<script src="js/popper.js"></script>
		<script src="js/plugins/bootstrap.min.js"></script>
		<script src="js/plugins/jquery.magnific-popup.min.js"></script>
	</body>
</html>
<?php
} else {
header('Status: 301 Moved Permanently', false, 301);
header('Location: ./' . $_SESSION['Usuario']['pag_inicial']);

}
?>
