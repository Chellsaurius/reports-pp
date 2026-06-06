<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    //
    public function index()
    {
        return response()->json(
            Product::where('activo', true)
                ->get()
        );
    }

    public function show($id)
    {
        $product = Product::where('activo', true)
            ->find($id);

        if (!$product) {

            return response()->json([
                'error' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($product);
    }

    private const CATEGORIAS = [
        'electrónica',
        'electronica',
        'hogar',
        'oficina'
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'categoria' => 'required|string',
            'precioUnitario' => 'required|numeric|gt:0',
            'stock' => 'nullable|integer|min:0'
        ]);
        
        $categoria = mb_strtolower(
            $validated['categoria']
        );

        if (!in_array($categoria, self::CATEGORIAS)) {
            return response()->json([
                'error' => 'Validación fallida',
                'detalles' => [
                    'Categoría no es válida. Valores aceptados: Electrónica, Hogar, Oficina'
                ]
            ], 400);
        }

        //$product = Product::create($validated);
        $product = Product::create([
            'name' => $validated['nombre'],
            'categoria' => ucfirst($categoria),
            'price' => $validated['precioUnitario'],
            'stock' => $validated['stock'] ?? 0
        ]);

        return response()->json($product, 201);
    }





    public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'nombre' => 'sometimes|string',
        'categoria' => 'sometimes|string',
        'precioUnitario' => 'sometimes|numeric|gt:0',
        'stock' => 'sometimes|integer|min:0'
    ]);

    $data = [];

    // nombre
    if (isset($validated['nombre'])) {
        $data['name'] = $validated['nombre'];
    }

    // categoria (solo si viene)
    if (isset($validated['categoria'])) {

        $categoria = mb_strtolower($validated['categoria']);

        if (!in_array($categoria, self::CATEGORIAS)) {
            return response()->json([
                'error' => 'Categoría no válida',
                'validas' => self::CATEGORIAS
            ], 400);
        }

        $data['categoria'] = $categoria;
    }

    // precio
    if (isset($validated['precioUnitario'])) {
        $data['price'] = $validated['precioUnitario'];
    }

    // stock
    if (isset($validated['stock'])) {
        $data['stock'] = $validated['stock'];
    }

    $product->update($data);

    return response()->json($product->refresh());
}









    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product || !$product->activo) {

            return response()->json([
                'error' => 'Producto no encontrado'
            ], 404);
        }

        $product->activo = false;

        $product->save();

        return response()->json([
            'mensaje' => 'Producto desactivado'
        ]);
    }

}
