<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Reporte de Inscripciones</title>

    <style>

        body{
            font-family: Arial;
            padding:40px;
            background:#f4f4f4;
        }

        table{
            width:100%;
            border-collapse: collapse;
            background:white;
        }

        th,td{
            border:1px solid #ccc;
            padding:10px;
            text-align:left;
        }

        th{
            background:#2563eb;
            color:white;
        }

        h1{
            margin-bottom:20px;
        }

        .btn{
            display:inline-block;
            margin-bottom:20px;
            padding:10px;
            background:#16a34a;
            color:white;
            text-decoration:none;
        }

        .btn-secondary{
            background:#2563eb;
        }

        .toolbar{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-bottom:20px;
        }

        .badge{
            display:inline-block;
            padding:6px 10px;
            border-radius:999px;
            font-size:12px;
            font-weight:bold;
            color:white;
        }

        .badge-ok{
            background:#16a34a;
        }

        .badge-bad{
            background:#dc2626;
        }

    </style>

</head>

<body>

<h1>Reporte de Inscripciones</h1>

<?php if (empty($modoExportacion)): ?>
    <div class="toolbar">
        <a href="index.php" class="btn btn-secondary">Volver al formulario</a>
        <a href="index.php?pagina=reporte&export=excel" class="btn">Exportar a Excel</a>
    </div>
<?php endif; ?>

<table>

    <thead>

        <tr>

            <th>ID</th>

            <th>Identidad</th>

            <th>Nombre</th>

            <th>Apellido</th>

            <th>Correo</th>

            <th>Celular</th>

            <th>Sexo</th>

            <th>Temas Tecnológicos</th>

            <th>Integridad</th>

        </tr>

    </thead>

    <tbody>

        <?php if(empty($registros)): ?>

            <tr>

                <td colspan="9">

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

                        <?= htmlspecialchars($fila["temas"]) ?>

                    </td>

                    <td>

                        <?php if (!empty($fila['integridad_ok'])): ?>
                            <span class="badge badge-ok">Validado</span>
                        <?php else: ?>
                            <span class="badge badge-bad">Corrompido</span>
                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

    </tbody>

</table>

</body>

</html>