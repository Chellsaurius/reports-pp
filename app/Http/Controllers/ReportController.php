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
    {
        $validated = $request->validate([
            'ventas' => 'required|array',
            'ventas.*.producto' => 'required|string',
            'ventas.*.precio' => 'required|numeric',
            'ventas.*.cantidad' => 'required|integer|min:1'
        ]);

        $resultado = [];

        $subtotalGeneral = 0;
        $descuentoGeneral = 0;
        $ivaGeneral = 0;
        $totalGeneral = 0;

        foreach ($validated['ventas'] as $venta) {

            $subtotal = $venta['precio'] * $venta['cantidad'];

            if ($subtotal <= 10000) {
                $descuento = $subtotal; 
            }
            elseif ($subtotal >= 10000.01 and $subtotal <= 20000) {
                $descuento = $subtotal * 0.10;
            }
            elseif ($subtotal >= 20000.01) {
                $descuento = $subtotal * 0.20;
            }

            $base = $subtotal - $descuento;

            $iva = $base * 0.16;

            $total = $base + $iva;

            $subtotalGeneral += $subtotal;
            $descuentoGeneral += $descuento;
            $ivaGeneral += $iva;
            $totalGeneral += $total;

            $resultado[] = [
                'producto' => $venta['producto'],
                'precio' => $venta['precio'],
                'cantidad' => $venta['cantidad'],
                'subtotal' => round($subtotal, 2),
                'descuento' => round($descuento, 2),
                'iva' => round($iva, 2),
                'total' => round($total, 2)
            ];
        }

        $response = [
            'ventas' => $resultado,
            'resumen' => [
                'subtotal_general' => round($subtotalGeneral, 2),
                'descuento_general' => round($descuentoGeneral, 2),
                'iva_general' => round($ivaGeneral, 2),
                'total_general' => round($totalGeneral, 2)
            ]
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
