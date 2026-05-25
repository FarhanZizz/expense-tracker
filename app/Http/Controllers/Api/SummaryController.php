<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\PaymentSource;

class SummaryController extends Controller
{
    public function index()
    {
        $sources = PaymentSource::all()->map(function ($source) {
            return [
                'source'      => $source->name,
                'balance'     => $source->balance,
                'total_in'    => Transaction::where('source_id', $source->id)->where('type', 'in')->sum('amount'),
                'total_out'   => Transaction::where('source_id', $source->id)->where('type', 'out')->sum('amount'),
            ];
        });

        return response()->json([
            'sources'        => $sources,
            'overall_in'     => Transaction::where('type', 'in')->sum('amount'),
            'overall_out'    => Transaction::where('type', 'out')->sum('amount'),
        ]);
    }

    public function monthly()
    {
        $month = request('month', now()->month);
        $year  = request('year', now()->year);

        return response()->json([
            'month'     => $month,
            'year'      => $year,
            'total_in'  => Transaction::where('type', 'in')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount'),
            'total_out' => Transaction::where('type', 'out')->whereMonth('date', $month)->whereYear('date', $year)->sum('amount'),
        ]);
    }
}