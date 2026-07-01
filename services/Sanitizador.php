<?php
/**
 * Clase estática para sanitización y limpieza de datos.
 * Esta clase se encarga de limpiar datos antes de validarlos
 * y antes de enviarlos al modelo.
 */

class Sanitizador
{
    public static function texto(string $valor): string
    {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        return preg_replace('/\s+/', ' ', $valor) ?? '';
    }

    public static function titulo(string $valor): string
    {
        $valor = self::texto($valor);
        $valor = mb_strtolower($valor, 'UTF-8');
        return mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8');
    }

    public static function correo(string $valor): string
    {
        $valor = trim($valor);
        $valor = filter_var($valor, FILTER_SANITIZE_EMAIL);
        return mb_strtolower($valor, 'UTF-8');
    }

    public static function celular(string $valor): string
    {
        $valor = trim($valor);
        return preg_replace('/[^0-9+\-\s]/', '', $valor) ?? '';
    }

    public static function entero(mixed $valor): int
    {
        return filter_var($valor, FILTER_VALIDATE_INT) !== false ? (int) $valor : 0;
    }

    public static function observaciones(string $valor): string
    {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        return mb_substr($valor, 0, 500, 'UTF-8');
    }

    public static function decimal(mixed $valor): float
    {
        $valor = is_string($valor) ? trim($valor) : $valor;

        if ($valor === '' || $valor === null) {
            return 0.0;
        }

        if (is_string($valor)) {
            $valor = str_replace([' ', ','], ['', '.'], $valor);
        }

        return (float) $valor;
    }

    public static function fecha(mixed $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $valor);

        if ($fecha === false || $fecha->format('Y-m-d') !== $valor) {
            return null;
        }

        return $fecha->format('Y-m-d');
    }

    public static function booleano(mixed $valor): int
    {
        $resultado = filter_var($valor, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $resultado === true ? 1 : 0;
    }

    public static function formularioColaborador(array $post): array
    {
        return [
            'identidad' => self::texto($post['identidad'] ?? ''),
            'nombre' => self::titulo($post['nombre'] ?? ''),
            'apellido' => self::titulo($post['apellido'] ?? ''),
            'edad' => self::entero($post['edad'] ?? 0),
            'tipo_sangre_id' => self::entero($post['tipo_sangre_id'] ?? 0),
            'sexo' => self::texto($post['sexo'] ?? ''),
            'nacionalidad_id' => self::entero($post['nacionalidad_id'] ?? 0),
            'ruta_colaborador_id' => self::entero($post['ruta_colaborador_id'] ?? 0),
            'correo' => self::correo($post['correo'] ?? ''),
            'celular' => self::celular($post['celular'] ?? ''),
            'observaciones' => self::observaciones($post['observaciones'] ?? ''),
        ];
    }

    public static function formularioPerfilLaboral(array $post): array
    {
        return [
            'colaborador_id' => self::entero($post['colaborador_id'] ?? 0),
            'tipo_empleado_id' => self::entero($post['tipo_empleado_id'] ?? 0),
            'planilla_id' => self::entero($post['planilla_id'] ?? 0),
            'ocupacion_id' => self::entero($post['ocupacion_id'] ?? 0),
            'salario' => self::decimal($post['salario'] ?? 0),
            'fecha_inicio' => self::fecha($post['fecha_inicio'] ?? ''),
            'fecha_fin' => self::fecha($post['fecha_fin'] ?? ''),
            'cargo_activo' => self::booleano($post['cargo_activo'] ?? false),
            'empleado_activo' => self::booleano($post['empleado_activo'] ?? true),
            'motivo_baja' => self::observaciones((string) ($post['motivo_baja'] ?? '')),
        ];
    }

    public static function formulario(array $post): array
    {
        return self::formularioColaborador($post);
    }
}