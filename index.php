<?php
require_once("./motores/interno/defs.php");
session_start();
$hayUsuario = (isset($_SESSION['Usuario']) && !($_SESSION['Usuario']['rol'] == 't' || $_SESSION['Usuario']['rol'] == 'n'));
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
			function firmado(event){
				//alert(event.keyCode);
				if(event.keyCode == 13 || event.keyCode == undefined){
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
							console.log(r);
							$("#mensaje").html("<div class='alert alert-danger' style='background-color: #e54343;color: #fff;'><strong>"+r.errmsg+" intente nuevamente</strong></div>");
							setTimeout(function(){
								$("#mensaje").html('');
							}, 4000);
						}
					});
					return false;
				}
			}
		</script>
	</head>
	<body class="fixed-footer">
		<header>
			<nav id="herramientas" class="navbar navbar-toggleable-md navbar-light bg-faded"  style="background-color: rgba(105, 100, 100, 0.73); height:120px;">
				<!--<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#opciones" aria-controls="opciones" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<a class="navbar-brand" href="#"><img src="img/logo-small.png" alt="logo"></a>
				<div id="opciones" class="collapse navbar-collapse">
					<ul class="nav navbar-nav navbar-right">
						<li class="nav-item"><a class="nav-link" href="registro.php">REGISTRARSE</a></li>
						<li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#firmado">INICIAR SESIÓN</a></li>
					</ul>
				</div>-->
				<div class="container">
					<!--<div class="row">-->
						<div class="col-md-8 col-sm-12 col-xs-12">
							<a class="navbar-brand" href="#"><img src="img/logo-small.png" alt="logo"></a>
						</div>
						<div class="col-md-2 col-sm-6">
							<a class="text-white pull-right hidden-xs hidden-sm" href="registro.php">REGISTRARSE</a>
							<a class="text-white pull-left hidden-lg hidden-md" href="registro.php">REGISTRARSE</a>
						</div>
						<div class="col-md-2 col-sm-6">
							<a class="text-white pull-right " href="#" data-toggle="modal" data-target="#firmado">INICIAR SESIÓN</a>
						</div>
					<!--</div>-->
				</div>
			</nav>
		</header>
		<!-- Intro Section -->
		<section class="intro" style="background-image: url(img/fondo.jpg);">
			<div class="container">
				<div class="row">
					<div class="col-md-10 col-sm-12 col-xs-12">
						<!--<img src="img/logo-fondo.png" />-->
					</div>
				</div>
				<div class="row">
					<div class="col-md-8 col-lg-8 col-sm-12 col-xs-12" id="divOfertas">
						<!-- Ofertas listadas -->
					</div>
					<div class="col-md-4 col-lg-4 col-s hidden-sm-down" id="divNube">
						<!-- nube de palabras -->
					</div>
				</div>
			</div>
		</section><!-- Intro Section End -->
		<!--Modal (Signin/Signup Page)-->
		<div class="modal fade" id="firmado">
			<div class="modal-dialog">
				<div class="container">
					<div class="modal-form">
						<div class="tab-content">
							<!-- Sign in form -->
							<form class="tab-pane transition scale fade in active" id="signin-form" autocomplete="off" onkeypress="firmado(event)">
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
									<button type="button" value="enviar" onclick="firmado(undefined)" class="btn-round btn-ghost btn-success pull-right">
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
		<!-- Footer -->
		<!-- Javascript (jQuery) Libraries and Plugins -->
		<script src="js/popper.js"></script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>
<?php
} else {
	header('Status: 301 Moved Permanently', false, 301);
	header('Location: ./' . $_SESSION['Usuario']['pag_inicial']);
}
?>
