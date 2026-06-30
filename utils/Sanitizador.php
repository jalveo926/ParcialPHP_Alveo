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

    public static function formulario(array $post): array
    {
        return [
            'identidad' => self::texto($post['identidad'] ?? ''),
            'nombre' => self::titulo($post['nombre'] ?? ''),
            'apellido' => self::titulo($post['apellido'] ?? ''),
            'edad' => self::entero($post['edad'] ?? 0),
            'sexo' => self::texto($post['sexo'] ?? ''),
            'pais_residencia_id' => self::entero($post['pais_residencia_id'] ?? 0),
            'nacionalidad_id' => self::entero($post['nacionalidad_id'] ?? 0),
            'correo' => self::correo($post['correo'] ?? ''),
            'celular' => self::celular($post['celular'] ?? ''),
            'observaciones' => self::observaciones($post['observaciones'] ?? ''),
            'temas' => isset($post['temas']) && is_array($post['temas'])
                ? array_map([self::class, 'entero'], $post['temas'])
                : []
        ];
    }
}