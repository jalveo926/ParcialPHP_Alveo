<?php

require_once __DIR__ . '/../models/InscriptorModel.php';
require_once __DIR__ . '/../services/Integridad.php';

class ReporteController
{
    public function mostrarReporte(): void
    {
        $modelo = new InscriptorModel();

        $registros = $this->aplicarAuditoria($modelo->obtenerReporte());
        $modoExportacion = ($_GET['export'] ?? '') === 'excel';

        if ($modoExportacion) {
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename=reporte_perfiles_laborales.xls');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        require_once __DIR__ . '/../views/reporte.php';
    }

    private function aplicarAuditoria(array $registros): array
    {
        foreach ($registros as &$fila) {
            $datos = [
                'codigo_empleado' => (string) ($fila['codigo_empleado'] ?? ''),
                'salario' => (string) ($fila['salario'] ?? ''),
                'tipo_empleado_id' => (string) ($fila['tipo_empleado_id'] ?? ''),
                'planilla_id' => (string) ($fila['planilla_id'] ?? ''),
                'ocupacion_id' => (string) ($fila['ocupacion_id'] ?? ''),
                'fecha_inicio' => (string) ($fila['fecha_inicio'] ?? ''),
            ];

            $firma = (string) ($fila['firma_integridad'] ?? '');

            $fila['integridad_ok'] = $firma !== '' && Integridad::verificar($datos, $firma);
        }

        unset($fila);

        return $registros;
    }
}

$controller = new ReporteController();

$controller->mostrarReporte();