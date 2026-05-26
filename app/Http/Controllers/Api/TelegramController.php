<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\PaymentSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    private $token;
    private $geminiKey;

    public function __construct()
    {
        $this->token      = env('TELEGRAM_BOT_TOKEN');
        $this->geminiKey  = env('GEMINI_API_KEY');
    }

    public function handle(Request $request)
    {
        $message = $request->input('message');
        if (!$message) return response()->json(['ok' => true]);

        $chatId = $message['chat']['id'];
        $text   = $message['text'] ?? '';

        $parsed = $this->parseWithGemini($text);
        $reply  = $this->processIntent($parsed);

        $this->sendMessage($chatId, $reply);
        return response()->json(['ok' => true]);
    }

    private function parseWithGemini($text)
{
    $systemPrompt = 'You are an expense tracking assistant. User sends messages in Bangla, English, or mixed Banglish. Extract info and reply ONLY with valid JSON, no extra text, no markdown backticks.
    {
      "intent": "add" or "fetch",
      "type": "in" or "out",
      "amount": number or null,
      "source": "bkash" or "bank" or "cash" or null,
      "category": guess from context or null,
      "period": "today" or "this_month" or "last_month" or null,
      "note": any extra detail or null
    }';

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
        'Content-Type'  => 'application/json',
    ])->post('https://api.groq.com/openai/v1/chat/completions', [
        'model'    => 'llama-3.1-8b-instant',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $text],
        ],
        'temperature' => 0,
    ]);

    $raw = $response->json('choices.0.message.content');
    \Log::info('Groq raw response: ' . $raw);
    $clean = preg_replace('/```json|```/', '', $raw);
    return json_decode(trim($clean), true);
}
    private function processIntent($parsed)
    {
        if (!$parsed) return "Bujhte parini. Arektu clearly bolo.";

        if ($parsed['intent'] === 'add') {
            return $this->addTransaction($parsed);
        }

        return $this->fetchSummary($parsed);
    }

    private function addTransaction($parsed)
    {
        $source = PaymentSource::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($parsed['source'] ?? '') . '%'])->first();

        if (!$source) {
            return "Source pailam na. bKash, bank, ba cash lekho.";
        }

        if (!$parsed['amount']) {
            return "Amount bolo nai. Koto taka?";
        }

        Transaction::create([
            'source_id' => $source->id,
            'type'      => $parsed['type'],
            'amount'    => $parsed['amount'],
            'note'      => $parsed['note'] ?? $parsed['category'] ?? '',
            'date'      => now()->toDateString(),
        ]);

        $source->balance += $parsed['type'] === 'in' ? $parsed['amount'] : -$parsed['amount'];
        $source->save();

        $typeText = $parsed['type'] === 'in' ? 'Income' : 'Expense';
        return "{$typeText} add hoise!\nSource: {$source->name}\nAmount: {$parsed['amount']} taka\nNote: " . ($parsed['note'] ?? $parsed['category'] ?? 'N/A');
    }

    private function fetchSummary($parsed)
    {
        $sources = PaymentSource::all();
        $reply   = "Summary:\n";

        foreach ($sources as $source) {
            $reply .= "\n{$source->name}: {$source->balance} taka";
        }

        $totalIn  = Transaction::where('type', 'in')->sum('amount');
        $totalOut = Transaction::where('type', 'out')->sum('amount');

        $reply .= "\n\nTotal In: {$totalIn} taka";
        $reply .= "\nTotal Out: {$totalOut} taka";

        return $reply;
    }

    private function sendMessage($chatId, $text)
    {
        Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $text,
        ]);
    }
}