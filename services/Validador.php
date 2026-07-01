<?php
/**
 * Clase estática para validar los datos del formulario.
 * Se validan campos obligatorios, formato de correo, edad,
 * celular, sexo y selección de temas tecnológicos.
 */

class Validador
{
    public static function validarColaborador(array $datos): array
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

        $tiposSangrePermitidos = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
        if (($datos['tipo_sangre_id'] ?? 0) <= 0 && empty($datos['tipo_sangre_id'])) {
            $errores[] = 'Debe seleccionar el tipo de sangre.';
        }

        $sexosPermitidos = ['Masculino', 'Femenino', 'Otro'];
        if (!in_array($datos['sexo'], $sexosPermitidos, true)) {
            $errores[] = 'Debe seleccionar un sexo válido.';
        }

        if ($datos['nacionalidad_id'] <= 0) {
            $errores[] = 'Debe seleccionar la nacionalidad.';
        }

        if ($datos['ruta_colaborador_id'] <= 0) {
            $errores[] = 'Debe seleccionar la ruta del colaborador.';
        }

        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Debe ingresar un correo válido.';
        }

        if ($datos['celular'] === '' || !preg_match('/^[0-9+\-\s]{7,20}$/', $datos['celular'])) {
            $errores[] = 'Debe ingresar un celular válido.';
        }

        if (mb_strlen($datos['observaciones'], 'UTF-8') > 500) {
            $errores[] = 'Las observaciones no deben superar los 500 caracteres.';
        }

        return $errores;
    }

    public static function validarPerfilLaboral(array $datos): array
    {
        $errores = [];

        if ($datos['colaborador_id'] <= 0) {
            $errores[] = 'Debe seleccionar el colaborador.';
        }

        if ($datos['tipo_empleado_id'] <= 0) {
            $errores[] = 'Debe seleccionar el tipo de empleado.';
        }

        if ($datos['planilla_id'] <= 0) {
            $errores[] = 'Debe seleccionar la planilla.';
        }

        if ($datos['ocupacion_id'] <= 0) {
            $errores[] = 'Debe seleccionar la ocupación.';
        }

        if ($datos['salario'] <= 0) {
            $errores[] = 'El salario debe ser mayor que cero.';
        }

        if (empty($datos['fecha_inicio'])) {
            $errores[] = 'La fecha de inicio es obligatoria.';
        }

        if (!empty($datos['fecha_fin']) && !empty($datos['fecha_inicio']) && $datos['fecha_fin'] < $datos['fecha_inicio']) {
            $errores[] = 'La fecha fin no puede ser menor que la fecha inicio.';
        }

        if (!empty($datos['fecha_fin']) && trim((string) $datos['motivo_baja']) === '') {
            $errores[] = 'Si existe fecha fin, debe indicar el motivo de la baja.';
        }

        if (mb_strlen((string) $datos['motivo_baja'], 'UTF-8') > 255) {
            $errores[] = 'El motivo de baja no debe superar 255 caracteres.';
        }

        return $errores;
    }

    public static function validarInscripcion(array $datos): array
    {
        return self::validarColaborador($datos);
    }
}