<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SaleProcessor;

class SaleController extends Controller
{
    //
    public function process(
        Request $request,
        SaleProcessor $processor
    )
    {
        return response()->json(
            $processor->process($request->all())
        );
    }
}
