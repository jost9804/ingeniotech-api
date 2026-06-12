<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private const CATEGORIES = ['Computadores', 'Celulares', 'Cámaras', 'Accesorios'];

    /**
     * Catálogo público: solo productos activos.
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($products);
    }

    /**
     * Listado para el panel admin: todos los productos.
     */
    public function adminIndex()
    {
        return response()->json(
            Product::query()->orderByDesc('created_at')->get()
        );
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Detalle público: solo si el producto está activo.
     */
    public function publicShow(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', $this->disk());
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $old = $product->getRawOriginal('image');
            if ($old) {
                Storage::disk($this->disk())->delete($old);
            }
            $data['image'] = $request->file('image')->store('products', $this->disk());
        }

        $product->update($data);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $image = $product->getRawOriginal('image');
        if ($image) {
            Storage::disk($this->disk())->delete($image);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado']);
    }

    private function disk(): string
    {
        return config('filesystems.product_disk');
    }

    /**
     * Genera una descripción de venta con IA (Gemini + búsqueda web),
     * investigando las características reales del producto a partir del nombre.
     */
    public function generateDescription(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:160',
        ]);

        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return response()->json([
                'message' => 'La generación con IA no está configurada (falta GEMINI_API_KEY).',
            ], 503);
        }

        $name = $validated['name'];
        $prompt = <<<PROMPT
        Eres un asistente de e-commerce para una tienda de tecnología en Colombia.
        Busca en la web las características reales del producto: "{$name}".

        Devuelve ÚNICAMENTE un objeto JSON válido (sin markdown, sin ```), con esta forma exacta:
        {
          "description": "Descripción de venta atractiva en español, máximo 55 palabras, tono cercano y profesional, sin emojis ni precio ni garantía.",
          "specs": [
            { "label": "Pantalla", "value": "6.5'' Super AMOLED 90Hz" },
            { "label": "Cámara", "value": "50MP" }
          ],
          "images": [
            "https://url-directa-de-una-imagen-del-producto.jpg"
          ]
        }

        Reglas:
        - "specs": entre 4 y 8 características técnicas reales (no inventes datos). Cada una con "label" y "value" cortos.
        - "images": hasta 4 URLs DIRECTAS a imágenes del producto (que terminen en .jpg, .jpeg, .png o .webp), encontradas en la web. Si no encuentras URLs directas y reales, deja la lista vacía []. No inventes URLs.
        - Responde solo el JSON, nada más.
        PROMPT;

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            $response = Http::timeout(45)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post($endpoint, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'tools' => [
                        ['google_search' => (object) []],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                    ],
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo contactar el servicio de IA. Intenta de nuevo.',
            ], 502);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'El servicio de IA devolvió un error. Intenta de nuevo.',
            ], 502);
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text'));

        if ($text === '') {
            return response()->json([
                'message' => 'La IA no devolvió resultados. Intenta con un nombre más específico.',
            ], 422);
        }

        $parsed = $this->parseAiJson($text);

        $description = trim((string) ($parsed['description'] ?? ''));
        if ($description === '') {
            // Si no vino JSON, usamos el texto crudo como descripción.
            $description = $text;
        }

        // Las imágenes vienen de Google Custom Search (fiables). Si no está
        // configurado o no hay resultados, usamos las que sugirió la IA.
        $images = $this->searchProductImages($name);
        if (empty($images)) {
            $images = $this->normalizeImageUrls($parsed['images'] ?? []);
        }

        return response()->json([
            'description' => $description,
            'specs' => $this->normalizeSpecs($parsed['specs'] ?? []),
            'images' => $images,
        ]);
    }

    /**
     * Busca imágenes del producto con Google Custom Search, las descarga y
     * las re-aloja en nuestro Supabase Storage (para que no se rompan por
     * hotlinking). Devuelve hasta 4 URLs propias. Vacío si no hay/ no sirven.
     */
    private function searchProductImages(string $name): array
    {
        $hosted = [];
        foreach ($this->customSearchImageUrls($name) as $url) {
            $rehosted = $this->rehostImage($url);
            if ($rehosted) {
                $hosted[] = $rehosted;
            }
            if (count($hosted) >= 4) {
                break;
            }
        }

        return $hosted;
    }

    /**
     * URLs candidatas (hasta 8) que Google Custom Search marca como imagen.
     */
    private function customSearchImageUrls(string $name): array
    {
        $key = config('services.google_search.key');
        $cx = config('services.google_search.cx');
        if (! $key || ! $cx) {
            return [];
        }

        try {
            $response = Http::timeout(20)->get('https://www.googleapis.com/customsearch/v1', [
                'key' => $key,
                'cx' => $cx,
                'q' => $name,
                'searchType' => 'image',
                'num' => 8,
                'safe' => 'active',
                'imgSize' => 'large',
            ]);
        } catch (\Throwable $e) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $out = [];
        foreach (data_get($response->json(), 'items', []) as $item) {
            $url = trim((string) data_get($item, 'link'));
            $mime = (string) data_get($item, 'mime');
            if ($url !== '' && str_starts_with($mime, 'image/') && str_starts_with($url, 'http')) {
                $out[] = $url;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Descarga una imagen externa y la sube a nuestro Storage.
     * Devuelve la URL propia, o null si no se pudo (404, no es imagen, etc.).
     */
    private function rehostImage(string $url): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(12)->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        $ext = match (true) {
            str_contains($contentType, 'jpeg'), str_contains($contentType, 'jpg') => 'jpg',
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            default => null,
        };
        if ($ext === null) {
            return null;
        }

        $body = $response->body();
        // Descarta respuestas vacías o sospechosamente pequeñas (no son fotos reales).
        if (strlen($body) < 2048) {
            return null;
        }

        $path = 'products/gallery/'.Str::random(40).'.'.$ext;
        try {
            Storage::disk($this->disk())->put($path, $body);
        } catch (\Throwable $e) {
            return null;
        }

        return Storage::disk($this->disk())->url($path);
    }

    /**
     * Extrae el objeto JSON de la respuesta de la IA (tolera ```json fences y texto alrededor).
     */
    private function parseAiJson(string $text): array
    {
        $clean = preg_replace('/^```(?:json)?|```$/m', '', $text);
        $decoded = json_decode(trim((string) $clean), true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: tomar el primer bloque {...} que aparezca.
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Normaliza specs a una lista de { label, value } con strings limpios.
     */
    private function normalizeSpecs($specs): array
    {
        if (! is_array($specs)) {
            return [];
        }

        $out = [];
        foreach ($specs as $spec) {
            $label = trim((string) data_get($spec, 'label'));
            $value = trim((string) data_get($spec, 'value'));
            if ($label !== '' && $value !== '') {
                $out[] = ['label' => $label, 'value' => $value];
            }
        }

        return array_slice($out, 0, 10);
    }

    /**
     * Conserva solo URLs http(s) que parezcan imágenes directas.
     */
    private function normalizeImageUrls($images): array
    {
        if (! is_array($images)) {
            return [];
        }

        $out = [];
        foreach ($images as $url) {
            $url = trim((string) $url);
            if (preg_match('#^https?://.+\.(jpe?g|png|webp|gif)(\?.*)?$#i', $url)) {
                $out[] = $url;
            }
        }

        return array_slice(array_values(array_unique($out)), 0, 4);
    }

    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:160',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'image' => 'nullable|image|max:4096',
            'specs' => 'nullable|string',
            'gallery' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // FormData manda strings; normalizamos los booleanos.
        $validated['featured'] = $request->boolean('featured');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        // specs y gallery llegan como JSON serializado; los decodificamos.
        $validated['specs'] = $this->decodeJsonField($request->input('specs'));
        $validated['gallery'] = $this->decodeJsonField($request->input('gallery'));

        // 'image' aquí solo es el archivo; el path se asigna aparte.
        unset($validated['image']);

        return $validated;
    }

    /**
     * Decodifica un campo JSON enviado como string desde FormData.
     * Devuelve un array (o null si viene vacío/ inválido).
     */
    private function decodeJsonField(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : null;
    }
}
