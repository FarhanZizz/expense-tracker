<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentSource;
use Illuminate\Http\Request;

class PaymentSourceController extends Controller
{
    public function index()
    {
        return response()->json(PaymentSource::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
        ]);

        $source = PaymentSource::create($request->only('name', 'type'));
        return response()->json($source, 201);
    }

    public function destroy($id)
    {
        PaymentSource::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}