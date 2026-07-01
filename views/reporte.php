
<?php

require_once __DIR__ . '/../helpers/ErrorHelper.php';
require_once __DIR__ . '/../models/InscriptorModel.php';

try {
    $modelo = new InscriptorModel();
    $registros = $modelo->obtenerReporte();
} catch (Throwable $e) {
    $errorSistema = $e;
    $registros = [];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reporte de Perfiles Laborales</title>

    <link rel="stylesheet" href="styles/style.css">

</head>

<body>

<main class="container">
<?php if (!empty($errorSistema)): ?>
    <?php mostrarError($errorSistema); ?>
<?php endif; ?>
<h1>Reporte de Perfiles Laborales</h1>

<?php if (empty($modoExportacion)): ?>
    <div>
        <a href="index.php">Volver al formulario</a>
        <br>
        <a href="index.php?pagina=reporte&export=excel">Exportar a Excel</a>
    </div>
<?php endif; ?>

<table class="reporte-tabla">

    <thead>

        <tr>

            <th>ID Perfil</th>

            <th>Codigo Empleado</th>

            <th>Identidad</th>

            <th>Nombre</th>

            <th>Apellido</th>

            <th>Correo</th>

            <th>Celular</th>

            <th>Sexo</th>

            <th>Tipo Empleado</th>

            <th>Planilla</th>

            <th>Ocupacion</th>

            <th>Salario</th>

            <th>Fecha Inicio</th>

            <th>Fecha Fin</th>

            <th>Cargo Activo</th>

            <th>Empleado Activo</th>

            <th>Motivo Baja</th>


            <th>Integridad</th>

        </tr>

    </thead>

    <tbody>

        <?php if(empty($registros)): ?>

            <tr>

                <td colspan="19">

                    No hay registros

                </td>

            </tr>

        <?php else: ?>

            <?php foreach($registros as $fila): ?>

                <tr>

                    <td>

                        <?= $fila["id"] ?>

                    </td>

                    <td>

                        <?= (int) $fila["codigo_empleado"] ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["identidad"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["nombre"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["apellido"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["correo"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["celular"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["sexo"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["tipo_empleado"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["planilla"]) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($fila["ocupacion"]) ?>

                    </td>

                    <td>

                        <?= number_format((float) $fila["salario"], 2) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars((string) $fila["fecha_inicio"]) ?>

                    </td>

                    <td>

                        <?= !empty($fila["fecha_fin"]) ? htmlspecialchars((string) $fila["fecha_fin"]) : 'N/A' ?>

                    </td>

                    <td>

                        <span class="<?= !empty($fila['cargo_activo']) ? 'estado-ok' : 'estado-bad' ?>">
                            <?= !empty($fila['cargo_activo']) ? 'Si' : 'No' ?>
                        </span>

                    </td>

                    <td>

                        <span class="<?= !empty($fila['empleado_activo']) ? 'estado-ok' : 'estado-bad' ?>">
                            <?= !empty($fila['empleado_activo']) ? 'Si' : 'No' ?>
                        </span>

                    </td>

                    <td>

                        <?= !empty($fila["motivo_baja"]) ? htmlspecialchars((string) $fila["motivo_baja"]) : 'Sin motivo' ?>

                    </td>

                    <td>

                        <span class="<?= !empty($fila['integridad_ok']) ? 'estado-ok' : 'estado-bad' ?>">
                            <?= !empty($fila['integridad_ok']) ? 'Validado' : 'Corrompido' ?>
                        </span>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

    </tbody>

</table>

<?php require_once __DIR__ . '/components/footer.php'; ?>

</main>

</body>

</html>