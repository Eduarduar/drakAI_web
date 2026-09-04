<?php

use Illuminate\Support\Facades\Route;

// La SPA de Vue maneja su propio enrutado en el cliente (ver
// resources/js/plugins/router/routes.js) — esta ruta solo sirve el shell
// HTML para cualquier ruta que no sea /api/*.
Route::view('/{any}', 'application')->where('any', '^(?!api).*$');
