
<?php
$pagina = $_GET['pagina'] ?? 'formulario';

if ($pagina === 'formulario') {
    require_once __DIR__ . '/views/formulario.php';
} elseif ($pagina === 'reporte') {
    require_once __DIR__ . '/controllers/ReporteController.php';
} else {
    echo "Página no encontrada.";
}

?>