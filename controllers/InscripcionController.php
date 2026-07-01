<?php

require_once __DIR__ . '/../models/InscriptorModel.php';
require_once __DIR__ . '/../services/Sanitizador.php';
require_once __DIR__ . '/../services/Validador.php';
require_once __DIR__ . '/../services/Integridad.php';

class InscripcionController
{
    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        try {
            $modelo = new InscriptorModel();
            $tipoFormulario = $_POST['tipo_formulario'] ?? '';

            if ($tipoFormulario === 'colaborador') {
                $datos = Sanitizador::formularioColaborador($_POST);
                $errores = Validador::validarColaborador($datos);

                if (!empty($errores)) {
                    $this->mostrarErrores($errores);
                    return;
                }

                $modelo->guardarColaborador($datos);

                header('Location: ../index.php?registro=colaborador_ok');
                exit();
            }

            if ($tipoFormulario === 'perfil_laboral') {
                $datos = Sanitizador::formularioPerfilLaboral($_POST);
                $colaborador = $modelo->obtenerColaboradorPorId((int) ($datos['colaborador_id'] ?? 0));

                if (empty($colaborador)) {
                    $this->mostrarErrores(['Debe seleccionar un colaborador existente.']);
                    return;
                }

                $datos['fecha_fin'] = !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null;
                $datos['motivo_baja'] = trim((string) ($datos['motivo_baja'] ?? ''));
                $datos['motivo_terminacion_id'] = !empty($_POST['motivo_terminacion_id'])
                    ? (int) $_POST['motivo_terminacion_id']
                    : null;

                if ($datos['fecha_fin'] !== null || $datos['motivo_baja'] !== '') {
                    $datos['cargo_activo'] = 0;
                    $datos['empleado_activo'] = 0;
                } else {
                    $datos['cargo_activo'] = (int) ($datos['cargo_activo'] ?? 1);
                    $datos['empleado_activo'] = (int) ($datos['empleado_activo'] ?? 1);
                }

                $errores = Validador::validarPerfilLaboral($datos);

                if ($datos['fecha_fin'] !== null && $datos['motivo_baja'] === '' && empty($datos['motivo_terminacion_id'])) {
                    $errores[] = 'Debe indicar un motivo de baja cuando coloca una fecha fin.';
                }

                if (!empty($errores)) {
                    $this->mostrarErrores($errores);
                    return;
                }

                $datosFirma = $this->datosParaFirmaPerfil($datos, $colaborador);
                $firma = Integridad::firmar($datosFirma);

                $modelo->guardarPerfilLaboral($datos, $firma);

                header('Location: ../index.php?registro=perfil_ok');
                exit();
            }

            $this->mostrarErrores(['Formulario no reconocido.']);
        } catch (Throwable $e) {
            $this->mostrarErrorSistema($e);
        }
    }

    private function datosParaFirmaPerfil(array $datos, array $colaborador): array
    {
        return [
            'codigo_empleado' => (string) ($colaborador['codigo_empleado'] ?? $datos['colaborador_id'] ?? ''),
            'identidad' => (string) ($colaborador['identidad'] ?? ''),
            'nombre' => (string) ($colaborador['nombre'] ?? ''),
            'apellido' => (string) ($colaborador['apellido'] ?? ''),
            'edad' => (string) ($colaborador['edad'] ?? ''),
            'tipo_sangre_id' => (string) ($colaborador['tipo_sangre_id'] ?? ''),
            'sexo' => (string) ($colaborador['sexo'] ?? ''),
            'nacionalidad_id' => (string) ($colaborador['nacionalidad_id'] ?? ''),
            'ruta_colaborador_id' => (string) ($colaborador['ruta_colaborador_id'] ?? ''),
            'correo' => (string) ($colaborador['correo'] ?? ''),
            'celular' => (string) ($colaborador['celular'] ?? ''),
            'salario' => (string) ($datos['salario'] ?? ''),
            'tipo_empleado_id' => (string) ($datos['tipo_empleado_id'] ?? ''),
            'planilla_id' => (string) ($datos['planilla_id'] ?? ''),
            'ocupacion_id' => (string) ($datos['ocupacion_id'] ?? ''),
            'fecha_inicio' => (string) ($datos['fecha_inicio'] ?? ''),
            'fecha_fin' => (string) ($datos['fecha_fin'] ?? ''),
            'cargo_activo' => (string) ($datos['cargo_activo'] ?? ''),
            'empleado_activo' => (string) ($datos['empleado_activo'] ?? ''),
            'motivo_terminacion_id' => (string) ($datos['motivo_terminacion_id'] ?? ''),
            'motivo_baja' => (string) ($datos['motivo_baja'] ?? ''),
        ];
    }

    private function mostrarErrores(array $errores): void
    {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<link rel="stylesheet" href="../styles/style.css">';
        echo '<title>Errores de validación</title></head><body><main class="container">';
        echo '<div class="error-box"><h3>Corrige los siguientes errores</h3><ul>';

        foreach ($errores as $error) {
            echo '<li>' . htmlspecialchars((string) $error) . '</li>';
        }

        echo '</ul></div>';
        echo '<p><a href="../index.php">Volver al formulario</a></p>';
        echo '</main></body></html>';
    }

    private function mostrarErrorSistema(Throwable $e): void
    {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<link rel="stylesheet" href="../styles/style.css">';
        echo '<title>Error del sistema</title></head><body><main class="container">';
        echo '<div class="error-box">';
        echo '<h3>Error del sistema o base de datos</h3>';
        echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Archivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Línea:</strong> ' . htmlspecialchars((string) $e->getLine()) . '</p>';
        echo '</div>';
        echo '<p><a href="../index.php">Volver al formulario</a></p>';
        echo '</main></body></html>';
    }
}

$controller = new InscripcionController();
$controller->registrar();
