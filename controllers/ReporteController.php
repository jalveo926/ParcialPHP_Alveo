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
            header('Content-Disposition: attachment; filename=reporte_inscripciones.xls');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        require_once __DIR__ . '/../views/reporte.php';
    }

    private function aplicarAuditoria(array $registros): array
    {
        foreach ($registros as &$fila) {
            $datos = [
                'identidad' => (string) ($fila['identidad'] ?? ''),
                'nombre' => (string) ($fila['nombre'] ?? ''),
                'apellido' => (string) ($fila['apellido'] ?? ''),
                'correo' => (string) ($fila['correo'] ?? ''),
                'celular' => (string) ($fila['celular'] ?? ''),
                'sexo' => (string) ($fila['sexo'] ?? ''),
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