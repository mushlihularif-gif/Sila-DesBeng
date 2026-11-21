<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Support\AssistantKnowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'nullable|string|in:user,model',
            'history.*.text' => 'nullable|string|max:4000',
        ]);

        $userMessage = trim((string) $request->message);
        $history = $request->input('history', []);

        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        if (empty($apiKey)) {
            return response()->json([
                'reply' => AssistantKnowledge::staticFallback($userMessage),
            ]);
        }

        $user = Auth::guard('sanctum')->user() ?: Auth::guard('web')->user();
        $systemInstruction = AssistantKnowledge::buildSystemInstruction($user);

        // Susun array contents untuk Gemini (harus dimulai dari 'user' dan bergantian user/model)
        $contents = [];
        foreach ($history as $msg) {
            $text = trim((string) ($msg['text'] ?? ''));
            if ($text === '' || empty($msg['role'])) {
                continue;
            }
            $role = $msg['role'] === 'user' ? 'user' : 'model';

            // Gemini mensyaratkan giliran pertama adalah 'user'
            if (empty($contents) && $role !== 'user') {
                continue;
            }

            // Gabungkan jika ada dua pesan berturut-turut dengan role yang sama
            $lastIndex = count($contents) - 1;
            if ($lastIndex >= 0 && $contents[$lastIndex]['role'] === $role) {
                $contents[$lastIndex]['parts'][0]['text'] .= "\n" . $text;
            } else {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        if (empty($contents) || end($contents)['role'] !== 'user') {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $userMessage]],
            ];
        } elseif (trim((string) end($contents)['parts'][0]['text']) !== $userMessage
            && !str_ends_with(trim((string) end($contents)['parts'][0]['text']), $userMessage)) {
            $contents[count($contents) - 1]['parts'][0]['text'] .= "\n" . $userMessage;
        }

        $requestBody = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.35,
                'topK' => 40,
                'topP' => 0.9,
                'maxOutputTokens' => 1200,
            ],
        ];

        // Daftar model valid yang akan dicoba (primary -> fallback)
        $models = array_values(array_unique(array_filter([
            $model,
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash',
        ])));

        $lastError = null;

        foreach ($models as $currentModel) {
            $maxRetries = 2;
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->timeout(25)->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$currentModel}:generateContent?key={$apiKey}",
                        $requestBody
                    );

                    if ($response->successful()) {
                        $data = $response->json();
                        $reply = $data['candidates'][0]['content']['parts'][0]['text']
                            ?? 'Maaf, saya belum menangkap maksudnya. Bisa ulangi pertanyaannya?';

                        return response()->json([
                            'reply' => $reply,
                        ]);
                    }

                    if (in_array($response->status(), [429, 503], true) && $attempt < $maxRetries) {
                        Log::warning("Gemini API {$response->status()} on model {$currentModel}, attempt {$attempt}. Retrying...");
                        sleep(2);
                        continue;
                    }

                    $lastError = $response->body();
                    Log::error("Gemini API Error ({$currentModel}, attempt {$attempt}): " . $response->body());
                    break;
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::error("Chatbot Exception ({$currentModel}, attempt {$attempt}): " . $e->getMessage());

                    if ($attempt < $maxRetries) {
                        sleep(1);
                        continue;
                    }
                    break;
                }
            }
        }

        Log::error('All Gemini models failed. Last error: ' . ($lastError ?? 'unknown'));

        return response()->json([
            'reply' => AssistantKnowledge::staticFallback($userMessage),
        ]);
    }
}
