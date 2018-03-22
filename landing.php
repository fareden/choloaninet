<?php
session_start();
require_once("./motores/interno/funciones.php");
require_once("./motores/interno/validador.php");
require_once("./motores/interno/formatos.php");
require_once("./motores/interno/defs.php");
require_once("./motores/interno/conexion.php");
$dbcon = conectaDB();
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
		<script type="text/javascript">
			function busca(pag) {
				pag = pag || "0";
				$.ajax({
					url: "./motores/hanumat.php",
					type: "POST",
					dataType: "JSON",
					data: {
						t: "<?= ofusca('oferta_mig'); ?>",
						r: "b",
						b: $("#txtBusca").val(),
						p: pag,
						l: '<?= ofusca("'1' = '1'") ?>'
					}
				}).done(function (r){
					//Renderear el resultado a la tabla
					if (r.error == "0") {
						dibuja(r.registros);
						$("#paginas").html(r.paginador);
					} else {
						//no hay registros
						errorDatos();
					}
				});
			}
			/*								while ($fila = $rs->fetch_row()) {
									$largoOfer = (strlen($fila[1]) > 10 ? "col-md-12" : "col-md-6 col-sm-12 col-xs-12");
									echo("<div class='fondo-gris1 rounded {$largoOfer}'>
										<div id='oferta' class='oferta rounded'>
											<h5 class='text-center mt-4'>{$fila[0]}</h5> 
											<p style='margin:12px;' class='text-justify'>
												{$fila[1]} 
											</p>
										</div>
									</div>");
								}
								?>
*/
			function dibuja(datos) {
				//console.log($("#frmAlta").serialize());
				$("#vista").html("");
				var contenido = "";
				for (var i = 0; i < datos.length; i++) {
					contenido += '<div class="fondo-gris1 rounded col-md-12"><div id="oferta_'+datos[i].id+'" class="oferta rounded">';
					contenido += "<h5 class='text-center mt-4'>"+datos[i].titulo+"</h5><p style='margin:12px;' class='text-justify'>"+datos[i].texto+"</p>";
					contenido += '<button onclick="interes(\'' + datos[i].id + '\')" data-toggle="modal" data-target="#verOferta">Me interesa!</button></div></div>';
				}
				$("#vista").html(contenido);
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
		</script>
	</head>
	<body onload="busca()">
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
							<div class=" rounded" style="background-color: rgba(50, 47, 47, 0.73); margin:8px;">
								<div class="col-md-8 col-sm-12 col-md-offset-2" style="margin-top:40px;">
									<img src="img/logo-largo.png" class="img-fluid">
									<h3 class="text-center" style="color:#eaa704;">¡Bienvenid@!</h3>
									<input type="text" id="txtBusca" class="col-md-8 form-control" placeholder=" Buscar ">
									<div class="col-md-2">
										<span class="input-group-btn">
											<button class="btn btn-default"  onclick="busca()" id="btnBusca" type="button"> <i class="material-icons align-middle">search</i> </button>
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="row" >
							<div class='fondo-gris1 rounded {$largoOfer}'>
								
							<div class="col-md-7 col-sm-12 mt-2 " id="vista">
							</div>
							<div class="col-md-5 mt-2 hidden-xs hidden-sm">
								<!--nube de palabras-->
								<div class="row">
									<div class="col-md-12 col-sm-12  fondo-gris1 rounded" style="height:800px;">
										<div id="nubePalabras" style="background:#fff;margin-top:10px;margin-bottom:10px; height:100%;" class="rounded"><span class="text-center">#nubePalabras</span></div>
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
		<script src="js/popper.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/plugins/jquery.magnific-popup.min.js"></script>
	</body>
</html>
