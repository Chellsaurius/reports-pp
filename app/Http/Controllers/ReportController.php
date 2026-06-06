<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesRequest;

class ReportController extends Controller
{
    //
    public function index()
    {
        return view('reports.reports_index');
    }

    public function sales(Request $request)
    {   //validacion de informacion
        $validated = $request->validate([
            '*.vendedor' => 'required|string',
            '*.producto' => 'required|string',
            '*.categoria' => 'required|string',
            '*.fecha' => 'required|date',
            '*.cantidad' => 'required|integer|min:1',
            '*.precioUnitario' => 'required|numeric'
        ]);

        /*
            DATOS QUE SOLICITAN
            Vendedor    
            Producto
            Categoría
            Fecha
            Cant.
            P. Unitario
            { "vendedor": "Ana",    "producto": "Laptop",   "categoria": "Electrónica", "fecha": "2025-03-05", "cantidad": 2,  "precioUnitario": 12000 },
        */

        
        $resultado = [];
        $resumenVendedores = [];
        $vendedorEstrella = null;

        $precioNeto = 0;
        $descuento = 0;
        $iva = 0;
        $subtotal = 0;
        $total = 0;

        foreach ($validated as $venta) {

            $precioNeto = $venta['precioUnitario'] * $venta['cantidad'];

            if ($venta['categoria'] == 'Electrónica' and $venta['cantidad'] >= 2) {
                $descuento = $precioNeto * 0.10; 
            }
            elseif ($venta['categoria'] == 'Hogar' and $venta['cantidad'] >= 3) {
                $descuento = $precioNeto * 0.05;
            }
            else {
                $descuento = 0;
            }

            $subtotal = $precioNeto - $descuento;

            $iva = $subtotal * 0.16;

            $total = $subtotal + $iva;

            $resultado[] = [
                'vendedor' => $venta['vendedor'],
                'producto' => $venta['producto'],
                'categoria' => $venta['categoria'],
                'fecha' => $venta['fecha'],
                'cantidad' => $venta['cantidad'],
                'precioUnitario' => $venta['precioUnitario'],
                'precioNeto' => round($precioNeto, 2),
                'descuento' => round($descuento, 2),
                'subtotal' => round($subtotal, 2),
                'iva' => round($iva, 2),
                'total' => round($total, 2)
            ];
            // parte para guardar los resultados de los vendedores
            $vendedor = $venta['vendedor'];

            if (!isset($resumenVendedores[$vendedor])) {

                $resumenVendedores[$vendedor] = [
                    'vendedor' => $vendedor,
                    'ventas' => 0,
                    'precio_neto' => 0,
                    'descuento' => 0,
                    'iva' => 0,
                    'total' => 0
                ];
            }

            $resumenVendedores[$vendedor]['ventas']++;
            $resumenVendedores[$vendedor]['precio_neto'] += $precioNeto;
            $resumenVendedores[$vendedor]['descuento'] += $descuento;
            $resumenVendedores[$vendedor]['iva'] += $iva;
            $resumenVendedores[$vendedor]['total'] += $total;

            

        }

        //a;adir vendedor estrella
        foreach ($resumenVendedores as $vendedor) {
            if ($vendedorEstrella === null || $vendedor['total'] > $vendedorEstrella['total']) {
                $vendedorEstrella = $vendedor;
            }
        }

        $vendedorEstrellaData = [
            'nombre' => $vendedorEstrella['vendedor'],
            'total_con_descuento' => round($vendedorEstrella['total'], 2),
            'total_sin_descuento' => round(
                $vendedorEstrella['precio_neto'] * 1.16,
                2
            )
        ];
        
        $response = [
            //'ventas' => $resultado,
            'resumen_vendedores' => array_values($resumenVendedores)
            , 'vendedor_estrella' => $vendedorEstrellaData
        ];

         SalesRequest::create([
            'payload' => $request->all(),
            'result' => $response,
            'ip' => $request->ip()
        ]);

        return response()->json($response);
    }
    
    public function summary()
    {
        $requests = SalesRequest::all();

        $subtotal = 0;
        $descuento = 0;
        $iva = 0;
        $total = 0;

        foreach ($requests as $request) {
            $subtotal += $request->result['resumen']['subtotal_general'] ?? 0;
            $descuento += $request->result['resumen']['descuento_general'] ?? 0;
            $iva += $request->result['resumen']['iva_general'] ?? 0;
            $total += $request->result['resumen']['total_general'] ?? 0;
        }

        return view('reports.summary', compact(
            'subtotal',
            'descuento',
            'iva',
            'total'
        ));
    }

    public function products()
    {
        $productos = [];

        foreach (SalesRequest::all() as $request) {
            foreach ($request->result['ventas'] ?? [] as $venta) {
                $nombre = $venta['producto'];
                if (!isset($productos[$nombre])) {
                    $productos[$nombre] = 0;
                }
                $productos[$nombre] += $venta['cantidad'];
            }
        }
        arsort($productos);

        return view('reports.products', compact('productos'));
    }

    public function daily()
    {
        $ventasPorDia = [];

        foreach (SalesRequest::all() as $request) {
            $fecha = $request->created_at->format('Y-m-d');
            if (!isset($ventasPorDia[$fecha])) {
                $ventasPorDia[$fecha] = 0;
            }
            $ventasPorDia[$fecha] +=
                $request->result['resumen']['total_general'] ?? 0;
        }

        ksort($ventasPorDia);

        return view('reports.daily', compact('ventasPorDia'));
    }
}
