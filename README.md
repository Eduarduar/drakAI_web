# drakAI

Aplicación web construida en **Laravel + Vue 3** para resumir documentos extensos de forma rápida y sencilla. El usuario sube un documento (o pega texto), elige cómo quiere el resumen y la IA —a través de la **Gemini API** de Google— lo genera en segundos. Sin cuentas de usuario ni base de datos: cada resumen se procesa al vuelo y no queda guardado en el servidor.

Proyecto desarrollado para la actividad universitaria "IA generativa aplicada", donde se define y construye una aplicación que use un modelo de IA generativa externo (Gemini) en vez de entrenar un modelo propio.

## Objetivo

Ofrecer una herramienta intuitiva y visualmente moderna donde el usuario pueda subir o pegar un documento extenso y recibir un resumen generado por IA en unos pocos pasos, con una experiencia guiada y sin fricción.

## Características principales

- **Flujo guiado de 4 pasos** (Documento → Configuración → Generando → Resultado), con un stepper que permite volver a corregir el documento o las opciones elegidas en cualquier momento.
- **Entrada flexible**: subir un archivo (PDF, Word `.docx` o texto plano) por arrastre o selección, o pegar el texto directamente.
- **Resumen personalizable**: agrupado por sección o como resumen general único, en formato de puntos clave o narrativo, con glosario de términos técnicos opcional.
- **Resultado accionable**: el resumen se puede copiar al portapapeles o descargar como `.txt`, y las opciones se pueden editar para regenerarlo sin volver a subir el documento.
- **Generación con Gemini** usando salida JSON estructurada y una configuración conservadora (`temperature = 0.2`) para priorizar la fidelidad al documento fuente y minimizar alucinaciones, especialmente en el glosario.

## Stack tecnológico

- **Backend:** Laravel 13 (PHP 8.3), API stateless (`/api/summarize`) sin autenticación ni base de datos.
- **IA / Resúmenes:** Gemini API (Google), vía `App\Services\Gemini\GeminiClient`.
- **Extracción de documentos:** `smalot/pdfparser` (PDF) y `phpoffice/phpword` (Word `.docx`).
- **Frontend:** Vue 3 + Vite, Vuetify + Tailwind CSS, Pinia, vue-router.
- **Componentes base:** librería propia de componentes reutilizables (`resources/js/components/Base`) + layout con menú lateral (`resources/js/@layouts`).
- **Entorno de desarrollo:** Docker vía Laravel Sail (solo `laravel.test` + `vite`, sin base de datos ni mail).

## Estado del proyecto

El flujo completo de resumen (frontend de 4 pasos + backend de extracción y llamada a Gemini) ya está construido y probado de punta a punta. Solo falta configurar una API key de Gemini para que funcione en un entorno nuevo — ver [Configurar Gemini](#configurar-gemini) abajo.

## Instalación

Sin base de datos ni envío de correo — la app no maneja datos de usuario, solo procesa documentos contra la API de Gemini. Los contenedores de Sail son solo dos: `laravel.test` (PHP) y `vite` (frontend).

```bash
cp .env.example .env   # si no existe ya

# composer/npm install se corren fuera de Docker una sola vez (o vía el
# contenedor de composer:2 si no tienes PHP local, ver más abajo)
composer install
npm install

php artisan key:generate   # si aún no tiene APP_KEY

./vendor/bin/sail up -d
```

La app queda disponible en `http://localhost:8000` (Vite sirve los assets en `http://localhost:5173`).

Si no tienes PHP/Composer instalados localmente, `composer install`/`key:generate` se pueden correr una vez con Docker:

```bash
docker run --rm -v "$PWD":/app -w /app -u "$(id -u):$(id -g)" composer:2 composer install
./vendor/bin/sail artisan key:generate
```

### Configurar Gemini

El resumen no funciona sin una API key de Gemini. Consigue una gratis en [Google AI Studio](https://aistudio.google.com/apikey) y agrégala a tu `.env`:

```bash
GEMINI_API_KEY=tu-api-key
GEMINI_MODEL=gemini-3.6-flash   # opcional, ya viene con este valor por defecto
```

No hace falta reiniciar los contenedores: Laravel lee el `.env` en cada request.

## Licencia

Por definir.
