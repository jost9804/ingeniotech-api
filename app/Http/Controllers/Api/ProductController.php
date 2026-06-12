<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:160',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'image' => 'nullable|image|max:4096',
            'featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // FormData manda strings; normalizamos los booleanos.
        $validated['featured'] = $request->boolean('featured');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        // 'image' aquí solo es el archivo; el path se asigna aparte.
        unset($validated['image']);

        return $validated;
    }
}
