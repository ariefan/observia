<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class GeminiService
{
    private Client $httpClient;
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        
        // Try multiple ways to get the API key to handle environment caching issues
        $envKey = env('GEMINI_API_KEY');
        $envVarKey = $_ENV['GEMINI_API_KEY'] ?? null;
        $getEnvKey = getenv('GEMINI_API_KEY');
        
        // Read from .env file directly to get the most current value
        $fileKey = '';
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            if (preg_match('/GEMINI_API_KEY="?([^"\r\n]*)"?/', $envContent, $matches)) {
                $fileKey = trim($matches[1], '"');
            }
        }
        
        // Use the file key if it's different from env (handles caching issues)
        $this->apiKey = $fileKey ?: $envKey ?: $envVarKey ?: $getEnvKey ?: '';
        
        Log::info('GeminiService API Key Loading', [
            'env_key' => $envKey ? substr($envKey, 0, 10) . '...' : 'null',
            'env_var_key' => $envVarKey ? substr($envVarKey, 0, 10) . '...' : 'null',
            'getenv_key' => $getEnvKey ? substr($getEnvKey, 0, 10) . '...' : 'null',
            'file_key' => $fileKey ? substr($fileKey, 0, 10) . '...' : 'null',
            'final_key' => $this->apiKey ? substr($this->apiKey, 0, 10) . '...' : 'null',
        ]);
    }

    public function isEnabled(): bool
    {
        return !empty($this->apiKey) && Setting::getValue('telegram_ai_chatbot_enabled', false);
    }

    public function generateResponse(string $message, string $userId = null): ?string
    {
        if (!$this->isEnabled()) {
            Log::info('Gemini AI service is disabled or API key not configured');
            return null;
        }

        try {
            $systemInstruction = $this->getSystemInstruction();
            $fullPrompt = $systemInstruction . "\n\nUser: " . $message . "\n\nAssistant:";

            $response = $this->httpClient->post($this->baseUrl, [
                'query' => [
                    'key' => $this->apiKey
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $fullPrompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                        'stopSequences' => []
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = trim($data['candidates'][0]['content']['parts'][0]['text']);
                
                Log::info('Gemini AI response generated', [
                    'user_id' => $userId,
                    'message_length' => strlen($message),
                    'response_length' => strlen($responseText)
                ]);
                
                return $responseText;
            }

            Log::warning('Gemini AI response format unexpected', ['data' => $data]);
            return null;

        } catch (RequestException $e) {
            Log::error('Gemini API request failed: ' . $e->getMessage(), [
                'user_id' => $userId,
                'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
                'response_body' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Gemini AI service error: ' . $e->getMessage(), [
                'user_id' => $userId
            ]);
            return null;
        }
    }

    private function getSystemInstruction(): string
    {
        $customInstruction = Setting::getValue('telegram_ai_bot_instruction', '');
        
        $defaultInstruction = "You are an AI assistant for an AI Farm management system. Your role is to help users with questions about livestock farming, animal health, breeding, feeding, and farm management. 

Key guidelines:
- Be helpful and informative about farming topics
- Keep responses concise and practical 
- If asked about technical farm data, explain that you can provide general guidance but users should check their system dashboard for specific data
- Respond in Indonesian language (Bahasa Indonesia) as this is an Indonesian farm management system
- Be friendly and professional
- If asked about non-farming topics, gently redirect to farming-related discussions";

        return $customInstruction ?: $defaultInstruction;
    }

    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Gemini API key tidak dikonfigurasi'
            ];
        }

        try {
            $testMessage = "Hello, this is a connection test.";
            $response = $this->generateResponse($testMessage);
            
            if ($response) {
                return [
                    'success' => true,
                    'message' => 'Koneksi ke Gemini API berhasil',
                    'test_response' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gemini API tidak merespons dengan benar'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Gemini API: ' . $e->getMessage()
            ];
        }
    }

    public function getUsageStats(): array
    {
        // This would typically connect to Gemini API usage stats
        // For now, return placeholder data
        return [
            'requests_today' => 0,
            'tokens_used_today' => 0,
            'quota_remaining' => 'Unknown',
            'last_request' => null
        ];
    }
}