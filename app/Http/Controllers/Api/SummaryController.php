<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Documents\DocumentTextExtractor;
use App\Services\Gemini\GeminiClient;
use App\Services\Gemini\GeminiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SummaryController extends Controller
{
    public function store(Request $request, DocumentTextExtractor $extractor, GeminiClient $gemini): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required_without:text', 'file', 'mimes:pdf,docx,txt', 'max:15360'],
            'text' => ['required_without:file', 'string', 'min:50', 'max:30000'],
            'grouping' => ['required', 'in:section,general'],
            'format' => ['required', 'in:bullets,narrative'],
            'glossary' => ['required', 'boolean'],
        ], [
            'file.required_without' => 'Sube un documento o pega el texto a resumir.',
            'file.mimes' => 'Solo se aceptan archivos PDF, Word (.docx) o texto plano (.txt).',
            'file.max' => 'El archivo no puede superar los 15 MB.',
            'text.required_without' => 'Sube un documento o pega el texto a resumir.',
            'text.min' => 'El texto pegado es demasiado corto para resumir.',
            'text.max' => 'El texto pegado no puede superar los 30.000 caracteres.',
            'grouping.required' => 'Elige cómo agrupar el resumen.',
            'grouping.in' => 'La agrupación elegida no es válida.',
            'format.required' => 'Elige el formato del resumen.',
            'format.in' => 'El formato elegido no es válido.',
            'glossary.required' => 'Indica si quieres incluir el glosario.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $documentText = $request->hasFile('file')
                ? $extractor->extract($request->file('file'))
                : (string) $validated['text'];
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo leer el contenido del documento. Prueba con otro archivo.',
            ], 422);
        }

        $documentText = trim($documentText);

        if ($documentText === '') {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo extraer texto del documento subido.',
            ], 422);
        }

        try {
            $result = $gemini->summarize($documentText, [
                'grouping' => $validated['grouping'],
                'format' => $validated['format'],
                'glossary' => (bool) $validated['glossary'],
            ]);
        } catch (GeminiException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
