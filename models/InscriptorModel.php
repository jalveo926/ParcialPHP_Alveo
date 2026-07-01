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
        $sql = "SELECT id, nombre FROM paises ORDER BY nombre";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerAreas(): array
    {
        $sql = "SELECT id, nombre FROM areas_interes ORDER BY nombre";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function guardar(array $datos, string $firma): int
    {
        $sql = "INSERT INTO inscriptores
                (
                    identidad,
                    nombre,
                    apellido,
                    edad,
                    sexo,
                    pais_residencia_id,
                    nacionalidad_id,
                    correo,
                    celular,
                    observaciones,
                    firma_integridad
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $datos["identidad"],
            $datos["nombre"],
            $datos["apellido"],
            $datos["edad"],
            $datos["sexo"],
            $datos["pais_residencia_id"],
            $datos["nacionalidad_id"],
            $datos["correo"],
            $datos["celular"],
            $datos["observaciones"],
            $firma
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function guardarTemas(int $inscriptorId, array $temas): void
    {
        $sql = "INSERT INTO inscriptor_temas
                (
                    inscriptor_id,
                    area_interes_id
                )
                VALUES (?, ?)";

        $stmt = $this->db->prepare($sql);

        foreach ($temas as $temaId) {
            $stmt->execute([$inscriptorId, $temaId]);
        }
    }

    public function obtenerReporte(): array
{
    $sql = "
        SELECT
            i.id,
            i.identidad,
            i.nombre,
            i.apellido,
            i.correo,
            i.celular,
            i.sexo,
            i.firma_integridad,

            COALESCE(
                GROUP_CONCAT(
                    a.nombre
                    SEPARATOR ', '
                ),
                'Sin temas'
            ) AS temas

        FROM inscriptores i

        LEFT JOIN inscriptor_temas it
            ON i.id = it.inscriptor_id

        LEFT JOIN areas_interes a
            ON a.id = it.area_interes_id

        GROUP BY
            i.id,
            i.identidad,
            i.nombre,
            i.apellido,
            i.correo,
            i.celular,
            i.sexo,
            i.firma_integridad

        ORDER BY i.id DESC
    ";

    $stmt = $this->db->query($sql);

    return $stmt->fetchAll();
}
}