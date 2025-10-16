<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 

class HomeController extends Controller
{
    public function index()
    {
        return view('main');
    }


    public function speak(Request $request)
    {
        // Validação
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $apiKey = env('GOOGLE_API_KEY');
        
        $endpoint = "https://texttospeech.googleapis.com/v1/text:synthesize?key={$apiKey}";


        $payload = [
            'input' => [
                'text' => $request->input('text'),
            ],
            'voice' => [
                'languageCode' => 'pt-BR',
                'ssmlGender' => 'FEMALE',
            ],
            'audioConfig' => [
                'audioEncoding' => 'MP3',
            ],
        ];
        // faz a requisição post
        try {
            $response = Http::acceptJson()
                            ->post($endpoint, $payload);
            if (!$response->successful()) {
                $body = $response->json();
                $msg = $body['error']['message'] ?? $response->body();
                return response()->json(['error' => "Erro da API Google: {$msg}"], 500);
            }

            $body = $response->json();

            if (!isset($body['audioContent'])) {
                return response()->json(['error' => 'Resposta inesperada da API: sem audioContent'], 500);
            }
            $base64Audio = $body['audioContent'];

            return response()->json([
                'audio_base64' => $base64Audio,
                'text' => $request->input('text'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage(),
            ], 500);
        }
    }
}
