<?php
function dameMenuApp() {
	$retval = '<ul class="nav navbar-nav navbar-right">
		<li class="nav-item"><a class="nav-link" href="inicio.php"> <i class="material-icons">home</i> </a></li>
		<li class="nav-item"><a class="nav-link" href="empresas.php">Empresas</a></li>
		<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" id="menuSistema" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Sistema</a>
			<div class="dropdown-menu" aria-labelledby="menuSistema">
				<a class="dropdown-item" href="usuarios.php">Usuarios</a>
				<a class="dropdown-item" href="roles.php">Roles de seguridad</a>
			</div>
		</li>
		<li class="nav-item"><a class="nav-link" href="#" onclick="doLogout()"> Salir </a></li>
	</ul>';
	return $retval;
}
?>
