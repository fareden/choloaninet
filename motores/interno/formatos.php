<?php
function dameMenuApp() {
	$retval = '<ul class="nav navbar-nav navbar-right">
		<li class="nav-item"><a class="navbar-brand" href="inicio.php"><img src="img/logo-small.png" alt="logo"></a>
		<li class="nav-item"><a class="text-white nav-link" href="migrantes.php">Migrantes</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="empresas.php">Empresas</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="directorio.php">Directorio</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="blog.php">Blog</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="reportes.php">Reportes</a></li>
		<li class="nav-item dropdown"><a class="text-white nav-link dropdown-toggle" href="#" id="menuSistema" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Sistema</a>
			<div class="dropdown-menu" aria-labelledby="menuSistema">
				<a class="dropdown-item" href="usuarios.php">Usuarios</a>
				<a class="dropdown-item" href="roles.php">Roles de seguridad</a>
			</div>
		</li>
		<li class="nav-item"><a class="text-white nav-link" href="#" onclick="doLogout()"> Salir </a></li>
	</ul>';
	return $retval;
}

function dameMenuMigrante() {
	$retval = '<ul class="nav navbar-nav navbar-right">
		<li class="nav-item"><a class="navbar-brand" href="landing.php"><img src="img/logo-small.png" alt="logo"></a>
		<li class="nav-item"><a class="text-white nav-link" href="emprende.php">Emprender</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="cv.php">Generar Currículum</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="mensajes.php">Mensajes</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="perfil.php">Mi cuenta</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="noticias.php">Noticias</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="dependencias.php">Directorio</a></li>
		<li class="nav-item"><a class="text-white nav-link" href="#" onclick="doLogout()">Salir</a></li>
	</ul>';
	return $retval;
}
function dameMenuGeneral() {
	$retval = '<div class="container">
		<div class="col-md-4 col-sm-12 col-xs-12">
			<a class="navbar-brand" href="index.php"><img src="img/logo-small.png" alt="logo"></a>
		</div>
		<div class="col-md-2 col-sm-6">
			<a class="text-white pull-right hidden-xs hidden-sm" href="noticias.php">NOTICIAS</a>
		</div>
		<div class="col-md-2 col-sm-6">
			<a class="text-white pull-right hidden-xs hidden-sm" href="dependencias.php">DIRECTORIO</a>
		</div>
		<div class="col-md-2 col-sm-6">
			<a class="text-white pull-right hidden-xs hidden-sm" href="registro.php">REGISTRARSE</a>
		</div>
		<div class="col-md-2 col-sm-6">
			<a class="text-white pull-right " href="#" data-toggle="modal" data-target="#firmado">INICIAR SESIÓN</a>
		</div>
	</div>';
	return $retval;
}
?>
