
<?php

require_once __DIR__ . '/../models/InscriptorModel.php';


$modelo = new InscriptorModel();

$paises = $modelo->obtenerPaises();

$tiposSangre = $modelo->obtenerTiposSangre();

$rutas = $modelo->obtenerRutasColaborador();

$tiposPlanilla = $modelo->obtenerTiposPlanilla();

$tiposEmpleado = $modelo->obtenerTiposEmpleado();

$ocupaciones = $modelo->obtenerOcupaciones();

$colaboradores = $modelo->obtenerColaboradores();

$registro = $_GET['registro'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Colaboradores</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<main class="container">

<?php if ($registro === 'colaborador_ok'): ?>
    <p>Colaborador registrado correctamente.</p>
<?php endif; ?>

<?php if ($registro === 'perfil_ok'): ?>
    <p>Perfil laboral registrado correctamente.</p>
<?php endif; ?>

<h2>Formulario de Colaborador</h2>

<form method="POST" action="controllers/InscripcionController.php">
    <input type="hidden" name="tipo_formulario" value="colaborador">

    <label>Identidad (Documento de Identificación)</label>
    <input type="text" name="identidad" maxlength="30" required>

    <label>Nombre</label>
    <input type="text" name="nombre" maxlength="100" required>

    <label>Apellido</label>
    <input type="text" name="apellido" maxlength="100" required>

    <label>Edad</label>
    <input type="number" name="edad" min="1" max="120" required>

    <label>Tipo de Sangre</label>
    <select name="tipo_sangre_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($tiposSangre as $tipo): ?>
            <option value="<?= (int) $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Sexo</label>
    <select name="sexo" required>
        <option value="">Seleccione</option>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
        <option value="Otro">Otro</option>
    </select>

    <label>Nacionalidad</label>
    <select name="nacionalidad_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($paises as $pais): ?>
            <option value="<?= (int) $pais['id'] ?>"><?= htmlspecialchars($pais['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Ruta del Colaborador</label>
    <select name="ruta_colaborador_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($rutas as $ruta): ?>
            <option value="<?= (int) $ruta['id'] ?>"><?= htmlspecialchars($ruta['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Correo</label>
    <input type="email" name="correo" maxlength="150" required>

    <label>Celular</label>
    <input type="text" name="celular" maxlength="20" required>

    <label>Observaciones</label>
    <textarea name="observaciones" maxlength="500"></textarea>

    <button type="submit">Guardar colaborador</button>
</form>

<hr>

<h2>Formulario de Perfil Laboral</h2>

<form method="POST" action="controllers/InscripcionController.php">
    <input type="hidden" name="tipo_formulario" value="perfil_laboral">

    <label>Colaborador (Código Empleado)</label>
    <select name="colaborador_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($colaboradores as $colaborador): ?>
            <option value="<?= (int) $colaborador['codigo_empleado'] ?>">
                #<?= (int) $colaborador['codigo_empleado'] ?> -
                <?= htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido']) ?>
                (<?= htmlspecialchars($colaborador['identidad']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label>Tipo de Empleado</label>
    <select name="tipo_empleado_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($tiposEmpleado as $tipo): ?>
            <option value="<?= (int) $tipo['id'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Planilla</label>
    <select name="planilla_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($tiposPlanilla as $planilla): ?>
            <option value="<?= (int) $planilla['id'] ?>"><?= htmlspecialchars($planilla['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Puesto (Ocupación)</label>
    <select name="ocupacion_id" required>
        <option value="">Seleccione</option>
        <?php foreach ($ocupaciones as $ocupacion): ?>
            <option value="<?= (int) $ocupacion['id'] ?>"><?= htmlspecialchars($ocupacion['nombre']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Salario</label>
    <input type="number" name="salario" min="0" step="0.01" required>

    <label>Fecha Inicio</label>
    <input type="date" name="fecha_inicio" required>

    <label>Fecha Fin (si aplica)</label>
    <input type="date" name="fecha_fin">

    <label>Cargo Activo</label>
    <select name="cargo_activo" required>
        <option value="1" selected>Sí</option>
        <option value="0">No</option>
    </select>

    <label>Empleado Activo</label>
    <select name="empleado_activo" required>
        <option value="1" selected>Sí</option>
        <option value="0">No</option>
    </select>

    <label>Motivo de baja (obligatorio si hay fecha fin)</label>
    <textarea name="motivo_baja" maxlength="255"></textarea>

    <button type="submit">Guardar perfil laboral</button>
</form>

<p>
    <a href="index.php?pagina=reporte">Ver Reporte</a>
</p>

<?php require_once __DIR__ . '/components/footer.php'; ?>

</main>
</body>
</html>