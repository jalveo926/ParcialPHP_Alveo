<?php

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../config/Conexion.php';

class InscriptorModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    public function obtenerPaises(): array
    {
        $sql = 'SELECT id, nombre FROM paises ORDER BY nombre';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerAreas(): array
    {
        $sql = 'SELECT id, nombre FROM areas_interes ORDER BY nombre';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerTiposSangre(): array
    {
        $sql = 'SELECT id, nombre FROM cat_tipos_sangre ORDER BY nombre';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerRutasColaborador(): array
    {
        $sql = 'SELECT id, nombre FROM cat_rutas_colaborador ORDER BY nombre';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerTiposPlanilla(): array
    {
        $sql = 'SELECT id, nombre FROM cat_tipos_planilla ORDER BY id';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerTiposEmpleado(): array
    {
        $sql = 'SELECT id, nombre FROM cat_tipos_empleado ORDER BY nombre';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerOcupaciones(): array
    {
        $sql = 'SELECT id, nombre FROM cat_ocupaciones ORDER BY nombre';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerColaboradores(): array
    {
        $sql = '
            SELECT
                codigo_empleado,
                identidad,
                nombre,
                apellido
            FROM colaboradores
            ORDER BY codigo_empleado DESC
        ';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function guardarColaborador(array $datos, string $firma): int
    {
        $sql = 'INSERT INTO colaboradores
                (
                    identidad,
                    nombre,
                    apellido,
                    edad,
                    tipo_sangre_id,
                    sexo,
                    nacionalidad_id,
                    ruta_colaborador_id,
                    correo,
                    celular,
                    observaciones
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $datos['identidad'],
            $datos['nombre'],
            $datos['apellido'],
            $datos['edad'],
            $datos['tipo_sangre_id'],
            $datos['sexo'],
            $datos['nacionalidad_id'],
            $datos['ruta_colaborador_id'],
            $datos['correo'],
            $datos['celular'],
            $datos['observaciones'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function guardarPerfilLaboral(array $datos, string $firma): int
    {
        $this->db->beginTransaction();

        try {
            if (!empty($datos['cargo_activo'])) {
                $this->desactivarPerfilesActivos((int) $datos['colaborador_id']);
            }

            $sql = 'INSERT INTO perfiles_laborales
                    (
                        colaborador_id,
                        tipo_empleado_id,
                        planilla_id,
                        ocupacion_id,
                        salario,
                        fecha_inicio,
                        fecha_fin,
                        cargo_activo,
                        empleado_activo,
                        motivo_baja,
                        firma_integridad
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['colaborador_id'],
                $datos['tipo_empleado_id'],
                $datos['planilla_id'],
                $datos['ocupacion_id'],
                $datos['salario'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['cargo_activo'],
                $datos['empleado_activo'],
                $datos['motivo_baja'],
                $firma,
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();

            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    private function desactivarPerfilesActivos(int $colaboradorId): void
    {
        $sql = '
            UPDATE perfiles_laborales
            SET cargo_activo = 0
            WHERE colaborador_id = ?
              AND cargo_activo = 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$colaboradorId]);
    }

    public function obtenerReporte(): array
    {
        $sql = '
            SELECT
                pl.id,
                c.codigo_empleado,
                c.identidad,
                c.nombre,
                c.apellido,
                c.correo,
                c.celular,
                c.sexo,
                te.id AS tipo_empleado_id,
                tp.id AS planilla_id,
                o.id AS ocupacion_id,
                te.nombre AS tipo_empleado,
                tp.nombre AS planilla,
                o.nombre AS ocupacion,
                pl.salario,
                pl.fecha_inicio,
                pl.fecha_fin,
                pl.cargo_activo,
                pl.empleado_activo,
                pl.motivo_baja,
                pl.firma_integridad,
                COALESCE(
                    GROUP_CONCAT(
                        a.nombre
                        ORDER BY a.nombre
                        SEPARATOR ", "
                    ),
                    "Sin temas"
                ) AS temas
            FROM perfiles_laborales pl
            INNER JOIN colaboradores c
                ON c.codigo_empleado = pl.colaborador_id
            INNER JOIN cat_tipos_empleado te
                ON te.id = pl.tipo_empleado_id
            INNER JOIN cat_tipos_planilla tp
                ON tp.id = pl.planilla_id
            INNER JOIN cat_ocupaciones o
                ON o.id = pl.ocupacion_id
            LEFT JOIN inscriptores i
                ON i.identidad = c.identidad
            LEFT JOIN inscriptor_temas it
                ON it.inscriptor_id = i.id
            LEFT JOIN areas_interes a
                ON a.id = it.area_interes_id
            GROUP BY
                pl.id,
                c.codigo_empleado,
                c.identidad,
                c.nombre,
                c.apellido,
                c.correo,
                c.celular,
                c.sexo,
                te.id,
                tp.id,
                o.id,
                te.nombre,
                tp.nombre,
                o.nombre,
                pl.salario,
                pl.fecha_inicio,
                pl.fecha_fin,
                pl.cargo_activo,
                pl.empleado_activo,
                pl.motivo_baja,
                pl.firma_integridad
            ORDER BY pl.id DESC
        ';

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function guardar(array $datos, string $firma): int
    {
        return $this->guardarColaborador($datos, $firma);
    }

    public function guardarTemas(int $inscriptorId, array $temas): void
    {
        $sql = 'INSERT INTO inscriptor_temas (inscriptor_id, area_interes_id) VALUES (?, ?)';
        $stmt = $this->db->prepare($sql);

        foreach ($temas as $temaId) {
            $stmt->execute([$inscriptorId, $temaId]);
        }
    }
}
