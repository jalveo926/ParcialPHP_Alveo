<?php

require_once __DIR__ . '/../models/InscriptorModel.php';
require_once __DIR__ . '/../services/Sanitizador.php';
require_once __DIR__ . '/../services/Validador.php';
require_once __DIR__ . '/../services/Integridad.php';

class InscripcionController
{
    public function registrar(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
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

                $firma = Integridad::firmar($datos);
                $modelo->guardarColaborador($datos, $firma);

                header('Location: ../index.php?registro=colaborador_ok');
                exit();
            }

            if ($tipoFormulario === 'perfil_laboral') {
                $datos = Sanitizador::formularioPerfilLaboral($_POST);
                $errores = Validador::validarPerfilLaboral($datos);

                if (!empty($errores)) {
                    $this->mostrarErrores($errores);
                    return;
                }

                $firma = Integridad::firmar($datos);
                $modelo->guardarPerfilLaboral($datos, $firma);

                header('Location: ../index.php?registro=perfil_ok');
                exit();
            }

            echo '<p>Formulario no reconocido.</p>';

        } catch (Exception $e) {

            echo "Error: " . $e->getMessage();
        }
    }

    private function mostrarErrores(array $errores): void
    {
        foreach ($errores as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
    }
}

$controller = new InscripcionController();

$controller->registrar();