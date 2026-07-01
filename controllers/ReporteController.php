<?php

require_once __DIR__ . '/../models/InscriptorModel.php';
require_once __DIR__ . '/../services/Integridad.php';

class ReporteController
{
    public function mostrarReporte(): void
    {
        try {
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
        } catch (Throwable $e) {
            $this->mostrarErrorSistema($e);
        }
    }

    private function aplicarAuditoria(array $registros): array
    {
        foreach ($registros as &$fila) {
            $datos = [
                'codigo_empleado' => (string) ($fila['codigo_empleado'] ?? ''),
                'identidad' => (string) ($fila['identidad'] ?? ''),
                'nombre' => (string) ($fila['nombre'] ?? ''),
                'apellido' => (string) ($fila['apellido'] ?? ''),
                'edad' => (string) ($fila['edad'] ?? ''),
                'tipo_sangre_id' => (string) ($fila['tipo_sangre_id'] ?? ''),
                'sexo' => (string) ($fila['sexo'] ?? ''),
                'nacionalidad_id' => (string) ($fila['nacionalidad_id'] ?? ''),
                'ruta_colaborador_id' => (string) ($fila['ruta_colaborador_id'] ?? ''),
                'correo' => (string) ($fila['correo'] ?? ''),
                'celular' => (string) ($fila['celular'] ?? ''),
                'salario' => (string) ($fila['salario'] ?? ''),
                'tipo_empleado_id' => (string) ($fila['tipo_empleado_id'] ?? ''),
                'planilla_id' => (string) ($fila['planilla_id'] ?? ''),
                'ocupacion_id' => (string) ($fila['ocupacion_id'] ?? ''),
                'fecha_inicio' => (string) ($fila['fecha_inicio'] ?? ''),
                'fecha_fin' => (string) ($fila['fecha_fin'] ?? ''),
                'cargo_activo' => (string) ($fila['cargo_activo'] ?? ''),
                'empleado_activo' => (string) ($fila['empleado_activo'] ?? ''),
                'motivo_terminacion_id' => (string) ($fila['motivo_terminacion_id'] ?? ''),
                'motivo_baja' => (string) ($fila['motivo_baja'] ?? ''),
            ];

            $firma = (string) ($fila['firma_integridad'] ?? '');

            $fila['integridad_ok'] = $firma !== '' && Integridad::verificar($datos, $firma);
        }

        unset($fila);

        return $registros;
    }

    private function mostrarErrorSistema(Throwable $e): void
    {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<link rel="stylesheet" href="../styles/style.css">';
        echo '<title>Error del reporte</title></head><body><main class="container">';
        echo '<div class="error-box">';
        echo '<h3>Error al generar el reporte</h3>';
        echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Línea:</strong> ' . htmlspecialchars((string) $e->getLine()) . '</p>';
        echo '</div>';
        echo '<p><a href="../index.php">Volver al formulario</a></p>';
        echo '</main></body></html>';
    }
}

$controller = new ReporteController();
$controller->mostrarReporte();
