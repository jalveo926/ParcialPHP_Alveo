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

        $datos = Sanitizador::formulario($_POST);

        $errores = Validador::validarInscripcion($datos);

        if (!empty($errores)) {

            foreach ($errores as $error) {
                echo "<p>" . $error . "</p>";
            }

            return;
        }

        try {

            $firma = Integridad::firmar($datos);

            $modelo = new InscriptorModel();

            $id = $modelo->guardar($datos, $firma);

            $modelo->guardarTemas($id, $datos["temas"]);

            header("Location: ../index.php?registro=ok");

            exit();

        } catch (Exception $e) {

            echo "Error: " . $e->getMessage();
        }
    }
}

$controller = new InscripcionController();

$controller->registrar();