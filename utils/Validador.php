<?php
/**
 * Clase estática para validar los datos del formulario.
 * Se validan campos obligatorios, formato de correo, edad,
 * celular, sexo y selección de temas tecnológicos.
 */

class Validador
{
    public static function validarInscripcion(array $datos): array
    {
        $errores = [];

        if ($datos['identidad'] === '') {
            $errores[] = 'La identidad es obligatoria.';
        }

        if ($datos['nombre'] === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($datos['apellido'] === '') {
            $errores[] = 'El apellido es obligatorio.';
        }

        if ($datos['edad'] < 1 || $datos['edad'] > 120) {
            $errores[] = 'La edad debe estar entre 1 y 120 años.';
        }

        $sexosPermitidos = ['Masculino', 'Femenino', 'Otro'];
        if (!in_array($datos['sexo'], $sexosPermitidos, true)) {
            $errores[] = 'Debe seleccionar un sexo válido.';
        }

        if ($datos['pais_residencia_id'] <= 0) {
            $errores[] = 'Debe seleccionar el país de residencia.';
        }

        if ($datos['nacionalidad_id'] <= 0) {
            $errores[] = 'Debe seleccionar la nacionalidad.';
        }

        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Debe ingresar un correo válido.';
        }

        if ($datos['celular'] === '' || !preg_match('/^[0-9+\-\s]{7,20}$/', $datos['celular'])) {
            $errores[] = 'Debe ingresar un celular válido.';
        }

        if (empty($datos['temas'])) {
            $errores[] = 'Debe seleccionar al menos UN tema tecnológico.';
        }

        if (mb_strlen($datos['observaciones'], 'UTF-8') > 500) {
            $errores[] = 'Las observaciones no deben superar los 500 caracteres.';
        }

        return $errores;
    }
}