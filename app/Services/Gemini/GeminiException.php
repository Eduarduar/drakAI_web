<?php

namespace App\Services\Gemini;

use RuntimeException;

/**
 * Cualquier fallo al pedirle un resumen a Gemini (key inválida, rate limit,
 * respuesta con forma inesperada...). El mensaje ya viene en español y listo
 * para mostrarse tal cual en el frontend.
 */
class GeminiException extends RuntimeException
{
}
