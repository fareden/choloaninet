<?php
require_once("defs.php");
$hayUsuario = (isset($_SESSION['Usuario']) && (isset($_SESSION['Usuario']['publica']) && $_SESSION['Usuario']['publica'] != "invalida"));
function redir($pagina) {
    error_log("Pide la redireccion a $pagina");
    header('Status: 301 Moved Permanently', false, 301);
    header("Location: $pagina");
    die();
    //Muelle, canalla
}

if (!$hayUsuario) {
    session_destroy();
    error_log(print_r($_SESSION['Usuario']));
    redir(PAG_DEFAULT);
    //var_dump($_SESSION);
} else {
    //FIXIT Por lo pronto todos tienen permisos
    if (!isset($_SESSION['Usuario']['permisos'][basename($_SERVER['PHP_SELF'], ".php")]) && FALSE) {
        //error_log("Entrando a página: " . basename($_SERVER['PHP_SELF'], ".php"));
        redir($_SESSION['Usuario']['pag_inicial']);
        //echo("permisos");
    }
}
?>