<?php
session_start();
require_once("./motores/interno/funciones.php");
//require_once("./motores/interno/MasterCat.class.php");
//require_once("./motores/interno/validador.php");
$_SESSION['Usuario'] = array("rol" => "n", "publica" => "nuevotemporal");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Nuevo usuario -- Zenbakia</title>
		<link rel="stylesheet" href="js/jquery-ui.min.css" />
		<!--CSS-->
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="css/personalizado.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>	

		<script src="js/jquery.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>
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
						}else if (r.error == '2') {
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
	<body>
		<div class="container">
			<div class="row" >
				<div id="agregaFotografo" tabindex="-1">
					<div class="fondo">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close limpia" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Por favor proporciona los siguientes datos</h4>
							</div>
							<div class="modal-body">
								<div id="mensaje"></div>
								<div id="forma">
									<form id="frmAlta" autocomplete="off">
										<table border="0">
											<tbody>
												<tr>
													<td>Correo electrónico:</td>
													<td><input class="form-control" type="email" id="idu" name="correo" /></td>
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
													<td><input type="text" id="nom" maxlength="200" name="ap" /></td>
												</tr>
												<tr>
													<td>Apellido materno:</td>
													<td><input type="text" id="nom" maxlength="200" name="am" /></td>
												</tr>
												<tr>
													<td>Fecha de nacimiento:</td>
													<td><input type="date" name="fn" /></td>
												</tr>
											</tbody>
										</table>
										<input type="hidden" name="r" value="l"/>
										<input type="hidden" name="alta" value="123jklsdjfkl23j4sfQWAAsdFD" />
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="limpia btn btn-default " onclick="window.history.back(1);">Cancelar</button>
								<a href="javascript:void(0)" class="btn btn-primary" onclick="agregaRegistro()">Agregar</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="js/popper.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/plugins/jquery.magnific-popup.min.js"></script>
	</body>
</html>