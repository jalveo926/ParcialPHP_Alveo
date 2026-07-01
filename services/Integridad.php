<?php
/**
 * Clase para firmar y verificar la integridad de los datos con OpenSSL.
 *
 * La firma permite detectar si un registro fue alterado después de guardarse.
 */

class Integridad
{
    private const PRIVATE_KEY = __DIR__ . '/../keys/private.pem';
    private const PUBLIC_KEY = __DIR__ . '/../keys/public.pem';

    private static function obtenerRutaOpenSSLConfig(): ?string
    {
        $rutas = [
            __DIR__ . '/../openssl.cnf',
            'D:/AplicacioneSoftware/Xamp/apache/conf/openssl.cnf',
            'D:/AplicacioneSoftware/Xamp/php/extras/ssl/openssl.cnf',
            'C:/xampp/apache/conf/openssl.cnf',
            'C:/xampp/php/extras/ssl/openssl.cnf',
        ];

        foreach ($rutas as $ruta) {
            if (file_exists($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    private static function erroresOpenSSL(): string
    {
        $errores = [];

        while ($mensaje = openssl_error_string()) {
            $errores[] = $mensaje;
        }

        return empty($errores) ? 'Sin detalle adicional de OpenSSL.' : implode(' | ', $errores);
    }

    private static function llavesValidas(): bool
    {
        if (!file_exists(self::PRIVATE_KEY) || !file_exists(self::PUBLIC_KEY)) {
            return false;
        }

        $privateContent = file_get_contents(self::PRIVATE_KEY);
        $publicContent = file_get_contents(self::PUBLIC_KEY);

        if ($privateContent === false || $publicContent === false) {
            return false;
        }

        $privateKey = openssl_pkey_get_private($privateContent);
        $publicKey = openssl_pkey_get_public($publicContent);

        return $privateKey !== false && $publicKey !== false;
    }

    private static function asegurarLlaves(): void
    {
        if (self::llavesValidas()) {
            return;
        }

        if (!is_dir(dirname(self::PRIVATE_KEY))) {
            mkdir(dirname(self::PRIVATE_KEY), 0777, true);
        }

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $rutaConfig = self::obtenerRutaOpenSSLConfig();

        if ($rutaConfig !== null) {
            $config['config'] = $rutaConfig;
        }

        $recurso = openssl_pkey_new($config);

        if ($recurso === false) {
            throw new RuntimeException(
                'No se pudieron generar las llaves OpenSSL. Ruta openssl.cnf usada: ' .
                ($rutaConfig ?? 'No encontrada') .
                '. Error: ' .
                self::erroresOpenSSL()
            );
        }

        $privateKey = '';

        $exportado = openssl_pkey_export($recurso, $privateKey, null, $config);

        if ($exportado === false || $privateKey === '') {
            throw new RuntimeException(
                'No se pudo exportar la llave privada. Error: ' . self::erroresOpenSSL()
            );
        }

        $detalles = openssl_pkey_get_details($recurso);

        if ($detalles === false || empty($detalles['key'])) {
            throw new RuntimeException(
                'No se pudo obtener la llave pública. Error: ' . self::erroresOpenSSL()
            );
        }

        file_put_contents(self::PRIVATE_KEY, $privateKey);
        file_put_contents(self::PUBLIC_KEY, $detalles['key']);
    }

    public static function crearCadena(array $datos): string
    {
        if (
            array_key_exists('codigo_empleado', $datos) ||
            array_key_exists('colaborador_id', $datos) ||
            array_key_exists('salario', $datos) ||
            array_key_exists('ocupacion_id', $datos)
        ) {
            $codigoEmpleado = $datos['codigo_empleado'] ?? $datos['colaborador_id'] ?? '';

            return implode('|', [
                trim((string) $codigoEmpleado),
                number_format((float) ($datos['salario'] ?? 0), 2, '.', ''),
                trim((string) ($datos['tipo_empleado_id'] ?? '')),
                trim((string) ($datos['planilla_id'] ?? '')),
                trim((string) ($datos['ocupacion_id'] ?? '')),
                trim((string) ($datos['fecha_inicio'] ?? '')),
            ]);
        }

        return implode('|', [
            trim((string) ($datos['identidad'] ?? '')),
            trim((string) ($datos['nombre'] ?? '')),
            trim((string) ($datos['apellido'] ?? '')),
            trim((string) ($datos['edad'] ?? '')),
            trim((string) ($datos['tipo_sangre_id'] ?? '')),
            trim((string) ($datos['sexo'] ?? '')),
            trim((string) ($datos['nacionalidad_id'] ?? '')),
            trim((string) ($datos['ruta_colaborador_id'] ?? '')),
            trim((string) ($datos['correo'] ?? '')),
            trim((string) ($datos['celular'] ?? '')),
        ]);
    }

    public static function firmar(array $datos): string
    {
        self::asegurarLlaves();

        $privateContent = file_get_contents(self::PRIVATE_KEY);
        $privateKey = openssl_pkey_get_private($privateContent);

        if ($privateKey === false) {
            throw new RuntimeException('La llave privada no es válida.');
        }

        $cadena = self::crearCadena($datos);
        $firma = '';

        $resultado = openssl_sign($cadena, $firma, $privateKey, OPENSSL_ALGO_SHA256);

        if ($resultado === false) {
            throw new RuntimeException('No se pudo firmar el registro. Error: ' . self::erroresOpenSSL());
        }

        return base64_encode($firma);
    }

    public static function verificar(array $datos, string $firmaGuardada): bool
    {
        self::asegurarLlaves();

        $publicContent = file_get_contents(self::PUBLIC_KEY);
        $publicKey = openssl_pkey_get_public($publicContent);

        if ($publicKey === false) {
            return false;
        }

        $cadena = self::crearCadena($datos);
        $firma = base64_decode($firmaGuardada, true);

        if ($firma === false) {
            return false;
        }

        $resultado = openssl_verify($cadena, $firma, $publicKey, OPENSSL_ALGO_SHA256);

        return $resultado === 1;
    }
}