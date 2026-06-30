<?php
/**
 * Helper pequeño para imprimir datos de forma segura en HTML.
 *
 * Aunque los datos se sanitizan al entrar, también se escapan al mostrarlos.
 * Esto ayuda a prevenir XSS en las vistas.
 */

class Vista
{
    public static function e(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}