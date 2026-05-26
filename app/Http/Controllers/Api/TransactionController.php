<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\PaymentSource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('source', 'category');

        if ($request->source_id) {
            $query->where('source_id', $request->source_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->month) {
            $query->whereMonth('date', $request->month)
                  ->whereYear('date', $request->year ?? now()->year);
        }

        return response()->json($query->latest('date')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:payment_sources,id',
            'type'      => 'required|in:in,out',
            'amount'    => 'required|numeric|min:0',
            'date'      => 'required|date',
        ]);

        $transaction = Transaction::create($request->only(
            'source_id', 'category_id', 'type', 'amount', 'note', 'date'
        ));

        // update source balance
        $source = PaymentSource::find($request->source_id);
        if ($request->type === 'in') {
            $source->balance += $request->amount;
        } else {
            $source->balance -= $request->amount;
        }
        $source->save();

        return response()->json($transaction->load('source', 'category'), 201);
    }

    public function destroy($id)
    {
        $transaction = Transaction::with('source')->findOrFail($id);

        // reverse the balance
        $source = PaymentSource::find($transaction->source_id);
        if ($transaction->type === 'in') {
            $source->balance -= $transaction->amount;
        } else {
            $source->balance += $transaction->amount;
        }
        $source->save();

        $transaction->delete();
        return response()->json(['message' => 'Deleted']);
    }
}