<?php

namespace App\Services\Gemini;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    // Decisión de ingeniería del equipo (ver docs/context/CONTEXTO.md,
    // slide "Tomen una decisión de ingeniería"): configuración
    // "Conservadora" — prioriza fidelidad al documento fuente y minimiza
    // alucinaciones en el glosario sobre creatividad de redacción. No es un
    // parámetro que el usuario final pueda tocar desde la UI.
    private const TEMPERATURE = 0.2;

    // Protege contra documentos desproporcionados (costo/latencia); muy por
    // encima de lo que ocupa un documento académico típico.
    private const MAX_DOCUMENT_CHARS = 300000;

    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {
    }

    /**
     * @param  array{grouping: string, format: string, glossary: bool}  $options
     * @return array{sections: array<int, array{title: ?string, content: array<int, string>}>, glossary: array<int, array{term: string, definition: string}>}
     */
    public function summarize(string $documentText, array $options): array
    {
        if ($this->apiKey === '') {
            throw new GeminiException('Falta configurar la API key de Gemini en el servidor (GEMINI_API_KEY en el .env).');
        }

        $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->timeout(120)
            ->post(sprintf(self::ENDPOINT, $this->model), [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $this->buildPrompt($this->truncate($documentText), $options)],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => self::TEMPERATURE,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->responseSchema(),
                ],
            ]);

        if ($response->failed()) {
            Log::warning('Gemini respondió con error', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new GeminiException(match (true) {
                $response->status() === 429 => 'Gemini está recibiendo demasiadas solicitudes en este momento. Intenta de nuevo en unos segundos.',
                in_array($response->status(), [400, 403], true) => 'La API key de Gemini configurada no es válida o no tiene permisos.',
                default => 'No se pudo generar el resumen. Intenta de nuevo más tarde.',
            });
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || $text === '') {
            Log::warning('Gemini devolvió una respuesta sin texto utilizable', ['body' => $response->json()]);

            throw new GeminiException('Gemini no devolvió una respuesta con el formato esperado.');
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded) || ! isset($decoded['sections']) || ! is_array($decoded['sections'])) {
            throw new GeminiException('No se pudo interpretar la respuesta de Gemini.');
        }

        return [
            'sections' => $decoded['sections'],
            'glossary' => is_array($decoded['glossary'] ?? null) ? $decoded['glossary'] : [],
        ];
    }

    private function truncate(string $documentText): string
    {
        return mb_substr($documentText, 0, self::MAX_DOCUMENT_CHARS);
    }

    /**
     * @param  array{grouping: string, format: string, glossary: bool}  $options
     */
    private function buildPrompt(string $documentText, array $options): string
    {
        $instructions = [
            'Eres un asistente que resume documentos extensos con fidelidad estricta al texto original.',
            'No inventes información, cifras, nombres ni conceptos que no estén presentes en el documento.',
            'Si el documento no alcanza para cubrir algo, omítelo en vez de inventarlo.',
        ];

        $instructions[] = $options['grouping'] === 'section'
            ? 'Organiza el resumen por sección: identifica las secciones o temas principales del documento y genera un bloque de resumen por cada una, usando como título el nombre o tema de esa sección.'
            : 'Genera un único resumen general que condense el documento completo, sin dividirlo por secciones (deja el título de esa sección vacío).';

        $instructions[] = $options['format'] === 'bullets'
            ? 'Cada bloque de resumen debe expresarse como una lista de puntos clave (viñetas), concisos y directos.'
            : 'Cada bloque de resumen debe expresarse en formato narrativo, como uno o más párrafos breves y claros.';

        $instructions[] = $options['glossary']
            ? 'Además, genera un glosario con los términos técnicos más importantes del documento y una definición breve de cada uno, basada únicamente en cómo se usan en el texto.'
            : 'No generes glosario: devuelve la lista "glossary" vacía.';

        $instructions[] = 'Documento a resumir (delimitado por las marcas ---DOCUMENTO---):';

        return implode("\n", $instructions)."\n---DOCUMENTO---\n{$documentText}\n---FIN DOCUMENTO---";
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'sections' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'nullable' => true],
                            'content' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                        'required' => ['content'],
                    ],
                ],
                'glossary' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'term' => ['type' => 'string'],
                            'definition' => ['type' => 'string'],
                        ],
                        'required' => ['term', 'definition'],
                    ],
                ],
            ],
            'required' => ['sections', 'glossary'],
        ];
    }
}
