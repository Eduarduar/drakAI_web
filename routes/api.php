<?php

use App\Http\Controllers\Api\SummaryController;
use Illuminate\Support\Facades\Route;

// Único endpoint de la app: recibe el documento/texto + las opciones del
// paso "Configurar resumen" del flujo del frontend y devuelve el resumen
// generado por Gemini. Sin auth ni estado — cada request es independiente
// (ver CLAUDE.md).
Route::post('/summarize', [SummaryController::class, 'store']);
