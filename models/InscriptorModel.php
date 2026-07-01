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
        $sql = 'SELECT id, nombre FROM cat_ocupaciones WHERE activo = 1 ORDER BY nombre';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerMotivosTerminacion(): array
    {
        $sql = 'SELECT id, nombre FROM cat_motivos_terminacion ORDER BY nombre';
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

    public function obtenerColaboradorPorId(int $codigoEmpleado): array
    {
        $sql = '
            SELECT
                codigo_empleado,
                identidad,
                nombre,
                apellido,
                edad,
                tipo_sangre_id,
                sexo,
                nacionalidad_id,
                ruta_colaborador_id,
                correo,
                celular
            FROM colaboradores
            WHERE codigo_empleado = ?
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$codigoEmpleado]);

        $colaborador = $stmt->fetch();

        return $colaborador !== false ? $colaborador : [];
    }

    public function guardarColaborador(array $datos, string $firma = ''): int
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
            (int) $datos['edad'],
            (int) $datos['tipo_sangre_id'],
            $datos['sexo'],
            (int) $datos['nacionalidad_id'],
            (int) $datos['ruta_colaborador_id'],
            $datos['correo'],
            $datos['celular'],
            $datos['observaciones'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function guardarPerfilLaboral(array $datos, string $firma): int
    {
        $this->db->beginTransaction();

        try {
            $fechaFin = !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null;
            $motivoBaja = trim((string) ($datos['motivo_baja'] ?? ''));
            $motivoTerminacionId = !empty($datos['motivo_terminacion_id'])
                ? (int) $datos['motivo_terminacion_id']
                : null;

            $cargoActivo = $fechaFin === null ? (int) ($datos['cargo_activo'] ?? 1) : 0;
            $empleadoActivo = $fechaFin === null && $motivoBaja === '' ? (int) ($datos['empleado_activo'] ?? 1) : 0;

            if ($cargoActivo === 1) {
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
                        motivo_terminacion_id,
                        motivo_baja,
                        firma_integridad
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                (int) $datos['colaborador_id'],
                (int) $datos['tipo_empleado_id'],
                (int) $datos['planilla_id'],
                (int) $datos['ocupacion_id'],
                (float) $datos['salario'],
                $datos['fecha_inicio'],
                $fechaFin,
                $cargoActivo,
                $empleadoActivo,
                $motivoTerminacionId,
                $motivoBaja !== '' ? $motivoBaja : null,
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
                c.edad,
                c.tipo_sangre_id,
                c.correo,
                c.celular,
                c.nacionalidad_id,
                c.ruta_colaborador_id,
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
                pl.motivo_terminacion_id,
                mt.nombre AS motivo_terminacion,
                pl.motivo_baja,
                pl.firma_integridad,
                "Sin temas" AS temas
            FROM perfiles_laborales pl
            INNER JOIN colaboradores c
                ON c.codigo_empleado = pl.colaborador_id
            INNER JOIN cat_tipos_empleado te
                ON te.id = pl.tipo_empleado_id
            INNER JOIN cat_tipos_planilla tp
                ON tp.id = pl.planilla_id
            INNER JOIN cat_ocupaciones o
                ON o.id = pl.ocupacion_id
            LEFT JOIN cat_motivos_terminacion mt
                ON mt.id = pl.motivo_terminacion_id
            ORDER BY pl.id DESC
        ';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function guardar(array $datos, string $firma): int
    {
        return $this->guardarColaborador($datos, $firma);
    }
}
