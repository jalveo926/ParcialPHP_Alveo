<?php

require_once __DIR__ . '/../models/InscriptorModel.php';


$modelo = new InscriptorModel();

$paises = $modelo->obtenerPaises();

$areas = $modelo->obtenerAreas();

?>

<form method="POST" action="controllers/InscripcionController.php">

    <input type="text" name="identidad" placeholder="Identidad" required>

    <input type="text" name="nombre" placeholder="Nombre" required>

    <input type="text" name="apellido" placeholder="Apellido" required>

    <input type="number" name="edad" min="1" max="120" required>

    <select name="sexo" required>
        <option value="">Sexo</option>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
        <option value="Otro">Otro</option>
    </select>


    <label>País residencia</label>

    <select name="pais_residencia_id">

        <?php foreach ($paises as $pais): ?>

            <option value="<?= $pais["id"] ?>">

                <?= $pais["nombre"] ?>

            </option>

        <?php endforeach; ?>

    </select>


    <label>Nacionalidad</label>

    <select name="nacionalidad_id">

        <?php foreach ($paises as $pais): ?>

            <option value="<?= $pais["id"] ?>">

                <?= $pais["nombre"] ?>

            </option>

        <?php endforeach; ?>

    </select>


    <input type="email" name="correo" placeholder="Correo">

    <input type="text" name="celular" placeholder="Celular">


    <textarea name="observaciones"></textarea>


    <h3>Temas</h3>

    <?php foreach ($areas as $area): ?>

        <label>

            <input type="checkbox" name="temas[]" value="<?= $area["id"] ?>">

            <?= $area["nombre"] ?>

        </label>

        <br>

    <?php endforeach; ?>


    <button type="submit">

        Enviar

    </button>
    <a href="index.php?pagina=reporte">

        Ver Reporte

    </a>
</form>