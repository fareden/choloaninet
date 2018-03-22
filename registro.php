<?php
session_start();
require_once("./motores/interno/defs.php");
require_once("./motores/interno/conexion.php");
require_once("./motores/interno/formatos.php");
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/MasterCat.class.php");
//require_once("./motores/interno/validador.php");
$_SESSION['Usuario'] = array("rol" => "n", "publica" => "nuevotemporal");
$migrante = new Catalogo("migrante", "P", null, "form-control");
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
		<link rel="stylesheet" type="text/css" href="css/personalizado.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<script type="text/javascript">
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
			function agregaRegistro() {
				if (valida()) {
					console.log($("#frmAlta").serialize());
					$.ajax({
						url: "motores/hanumat.php",
						type: "POST",
						dataType: "JSON",
						data: $("#frmAlta").serialize()
					}).done(function (r) {
						if (r.error == '0') {
							$("#mensaje").html("<div class='alert alert-success'> Registrado correctamente </div>");
							setTimeout(function(){
								window.location.href = 'index.php';
							}, 3000);
						}else {
							alert(r.errmsg);
							$("#mensaje").html("<div class='alert alert-danger'> Algo salió mal... </div>");
							setTimeout(function(){
								$("#mensaje").html('');
							}, 3000);
						}
					});
				}
				return false;
			}
			$(document).ready( function () {
				$(".limpia" ).click(function(e) {
					$("#frmAlta")[0].reset();
				 });
			});
			function valida() {
				var retval = true;
				var ctrls = $("input");
				for (var i = 0; i < ctrls.length; i++) {
					retval = (retval && ctrls[i].value != "");
				}
				return retval;
			}
		</script>
	</head>
	<body class="">
		<header>
			<nav id="herramientas" class="navbar navbar-toggleable-md"  style="background-color: rgba(50, 47, 47, 0.73); height:120px;">
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
				<?= dameMenuGeneral(); ?>
			</nav>
		</header>
		
		<!-- Intro Section -->
		<section class="intro" style="background-image: url(img/fondo2.jpg); min-height: 60%;"></section>
			
		</section>
		<!-- Intro Section End -->
		<section style="position: absolute; z-index:1; min-width:100%;">
			<div class="container" style=" margin-top:30px;">
				<div class="row">
					<div class="col-md-12 col-sm-12">
						<div class="row rounded fondo-gris1">
							<div class=" rounded" style="width: 100%;background-color: rgba(50, 47, 47, 0.73); margin:8px;">
								<div class="col-md-12 col-sm-12" style="margin-top:40px;">
									<center>
										<h3 style="color:#eaa704;">¡Regístrate!</h3>
										<h4>Por favor proporciona los siguientes datos</h4>
									</center>
								</div>
							</div>
						</div>
						<div class="row" >
							<div class="col-md-12 col-sm-12 mt-2 ">
								<div id="mensaje"></div>
								<div id="forma" style="" class="fondo-gris1 rounded">
									<form id="frmAlta" autocomplete="off" class="oferta rounded">
										<table border="0">
											<tbody>
												<tr>
													<td>Correo electrónico:</td>
													<td><input class="form-control" type="email" id="idu" name="idu" /></td>
												</tr>
												<tr>
													<td>Contraseña:</td>
													<td><input type="password" id="p1" maxlength="200" value="" onchange="validapwd()" /></td>
												</tr>
												<tr>
													<td>confirmar:</td>
													<td>
														<input type="password" id="p2" maxlength="200" value="" onchange="validapwd()" />
														<input type="hidden" id="elpass" name="contras" />
													</td>
												</tr>
												<tr>
													<td>Nombre(s):</td>
													<td><input type="text" id="nom" maxlength="200" name="no" /></td>
												</tr>
												<tr>
													<td>Apellido paterno:</td>
													<td><input type="text" id="app" maxlength="200" name="ap" /></td>
												</tr>
												<tr>
													<td>Apellido materno:</td>
													<td><input type="text" id="apm" maxlength="200" name="am" /></td>
												</tr>
												<tr>
													<td>Fecha de nacimiento:</td>
													<td><input type="date" name="fn" /></td>
												</tr>
												<tr>
													<td>Sexo:</td>
													<td>
														<select name="sx">
															<option value="-1">-- Seleccione uno --</option>
															<option value="Hombre">Hombre</option>
															<option value="Mujer">Mujer</option>
														</select>
													</td>
												</tr>
											</tbody>
										</table>
										<input type="hidden" name="r" value="l"/>
										<input type="hidden" name="alta" value="123jklsdjfkl23j4sfQWAAsdFD" />
									</form>
								</div>
								<button type="button" class="limpia btn btn-default " onclick="window.history.back(1);">Cancelar</button>
								<a href="javascript:void(0)" class="btn btn-primary" onclick="agregaRegistro()">Agregar</a>
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