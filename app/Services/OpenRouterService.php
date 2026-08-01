<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenRouterService
{
    public function getDevicePower(string $deviceName)
    {
       $response = Http::withHeaders([
    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
    'Content-Type'  => 'application/json',
    'HTTP-Referer' => 'http://localhost:8000',
    'X-Title' => 'Solar System Project',
])
        ->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'inclusionai/ling-3.0-flash:free',

            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an electrical engineer.

Return ONLY valid JSON.

Format:
{"device":"string","power":number}

Rules:
- device must be English
- power must be realistic watt value
- no explanation
- no markdown
- always return JSON'
                ],
                [
                    'role' => 'user',
                    'content' => $deviceName
                ]
            ],

            'temperature' => 0.2,
        ]);

        // 🔴 إذا فشل الطلب
        if (!$response->successful()) {
            return [
                'error' => true,
                'step' => 'http_failed',
                'status' => $response->status(),
                'response' => $response->body()
            ];
        }

        $data = $response->json();

        // 🔴 إذا ما في رد
        if (!isset($data['choices'][0]['message']['content'])) {
            return [
                'error' => true,
                'step' => 'no_choices',
                'raw' => $data
            ];
        }

        $content = $data['choices'][0]['message']['content'];

        // تنظيف الرد
        $content = trim($content);
        $content = preg_replace('/`json|```/', '', $content);

        $decoded = json_decode($content, true);

        // 🔴 إذا JSON خربان
        if (!$decoded) {
            return [
                'error' => true,
                'step' => 'invalid_json',
                'raw_content' => $content
            ];
        }

        return $decoded;
    }
}
