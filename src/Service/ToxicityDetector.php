<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ToxicityDetector
{
    private HttpClientInterface $client;
    private string $apiKey;
    private ?LoggerInterface $logger;
    private string $model;

    public function __construct(HttpClientInterface $client, string $apiKey, LoggerInterface $logger = null, string $model = 'gpt-4o-mini')
    {
        $this->client = $client;
        
        if (empty($apiKey)) {
            if ($logger) {
                $logger->error('OpenAI API key is empty or not configured');
            }
            throw new \InvalidArgumentException('OpenAI API key cannot be empty');
        }
        
        $this->apiKey = $apiKey;
        $this->logger = $logger;
        $this->model = $model;
        
        if ($logger) {
            $logger->info('ToxicityDetector initialized with API key: ' . substr($apiKey, 0, 5) . '...' . substr($apiKey, -5));
            $logger->info('Using model: ' . $model);
        }
    }

    public function analyzeToxicity(string $text): array
    {
        // Log the text being analyzed
        if ($this->logger) {
            $this->logger->info('Analyzing text for toxicity: ' . $text);
        }
        
        if (empty($text)) {
            return [
                'isToxic' => false,
                'toxicWords' => [],
                'reason' => 'Empty text'
            ];
        }
        
        // Enhanced profanity check - make case insensitive and more thorough
        $profanityList = [
            'fuck', 'shit', 'ass', 'bitch', 'porn', 'dick', 'pussy', 'cunt', 'bastard',
            'asshole', 'whore', 'slut', 'nigger', 'faggot', 'retard', 'cock', 'tits',
            'boobs', 'penis', 'vagina', 'anal', 'escort', 'piss', 'prick', 'twat', 'jizz',
            'hell', 'damn', 'sexy', 'hot', 'nude', 'naked', 'sex', 'erotic', 'seductive'
        ];
        
        $textLower = strtolower($text);
        $foundWords = [];
        
        foreach ($profanityList as $word) {
            // Check word boundaries to avoid false positives
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $textLower)) {
                $foundWords[] = $word;
                if ($this->logger) {
                    $this->logger->warning('Found profanity: ' . $word . ' in text');
                }
            }
        }
        
        if (!empty($foundWords)) {
            return [
                'isToxic' => true,
                'toxicWords' => $foundWords,
                'reason' => 'Contains explicit language or profanity'
            ];
        }
        
        // Try OpenAI API if no direct matches found
        try {
            if ($this->logger) {
                $this->logger->info('No direct profanity found, checking with API');
            }
            
            // If we get here and API fails, we'll just rely on our local check
            try {
                $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $this->model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a strict content moderator for an e-commerce platform. Your job is to detect any inappropriate or adult content in product listings.

Analyze the following text for:
1. Profanity or crude language
2. Sexual content or innuendo
3. Adult-oriented product descriptions
4. Provocative terminology

Respond ONLY with a valid JSON object containing:
{
  "isToxic": boolean,
  "toxicWords": [array of problematic words or phrases],
  "reason": "brief explanation"
}

If you\'re unsure, err on the side of caution and flag the content.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $text
                            ],
                        ],
                        'temperature' => 0.1,  // Lower temperature for more predictable responses
                    ],
                    'timeout' => 10,
                ]);

                $data = $response->toArray();
                
                if ($this->logger) {
                    $this->logger->info('Raw API response: ' . json_encode($data));
                }
                
                if (isset($data['choices'][0]['message']['content'])) {
                    $analysisText = $data['choices'][0]['message']['content'];
                    
                    if ($this->logger) {
                        $this->logger->info('API response content: ' . $analysisText);
                    }
                    
                    $analysis = json_decode($analysisText, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($analysis)) {
                        return $analysis;
                    } else {
                        if ($this->logger) {
                            $this->logger->error('JSON parsing error: ' . json_last_error_msg());
                        }
                    }
                } else {
                    if ($this->logger) {
                        $this->logger->error('Unexpected API response structure: ' . json_encode($data));
                    }
                }
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error('Error in toxicity API call: ' . $e->getMessage());
                    $this->logger->error('Error trace: ' . $e->getTraceAsString());
                }
                
                // Since API fails but we already did a local check, let's treat this as non-toxic
                return [
                    'isToxic' => false,
                    'toxicWords' => [],
                    'reason' => 'API failed, but no obvious profanity found in local check'
                ];
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Error in toxicity analysis: ' . $e->getMessage());
                $this->logger->error('Error trace: ' . $e->getTraceAsString());
            }
        }

        return [
            'isToxic' => false,
            'toxicWords' => [],
            'reason' => 'No inappropriate content detected'
        ];
    }

    public function testConnection(): bool
    {
        try {
            // Test a simpler endpoint with less permissions required
            $response = $this->client->request('GET', 'https://api.openai.com/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'timeout' => 5,
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($this->logger) {
                $this->logger->info('OpenAI API connection test status code: ' . $statusCode);
                
                // If successful, log the available models
                if ($statusCode >= 200 && $statusCode < 300) {
                    $data = $response->toArray();
                    if (isset($data['data']) && is_array($data['data'])) {
                        $models = array_map(function($model) {
                            return $model['id'] ?? 'unknown';
                        }, $data['data']);
                        $this->logger->info('Available models: ' . implode(', ', $models));
                    }
                }
            }
            
            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('OpenAI API connection test failed: ' . $e->getMessage());
            }
            return false;
        }
    }
    
    // Helper method to get available models
    public function getAvailableModels(): array
    {
        try {
            $response = $this->client->request('GET', 'https://api.openai.com/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'timeout' => 5,
            ]);
            
            $data = $response->toArray();
            $models = [];
            
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $model) {
                    if (isset($model['id'])) {
                        $models[] = $model['id'];
                    }
                }
            }
            
            return $models;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to get available models: ' . $e->getMessage());
            }
            return [];
        }
    }

    private function callOpenAI(string $text, string $preferredModel): ?array
    {
        $availableModels = [
            $preferredModel,
            'gpt-4o-mini',
            'o3-mini',
            'o1'
        ];
        
        $lastError = null;
        
        foreach ($availableModels as $model) {
            try {
                $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a content moderator. Analyze the following text for toxic content, profanity, or inappropriate language. Respond with a JSON object containing: isToxic (boolean), toxicWords (array of toxic words found), and reason (explanation).'
                            ],
                            [
                                'role' => 'user',
                                'content' => $text
                            ],
                        ],
                        'temperature' => 0.3,
                    ],
                    'timeout' => 10,
                ]);
                
                // If we got here, the request was successful
                if ($this->logger) {
                    $this->logger->info("Successfully used model: $model");
                }
                
                return $response->toArray();
            } catch (\Exception $e) {
                $lastError = $e;
                if ($this->logger) {
                    $this->logger->warning("Failed with model $model: " . $e->getMessage());
                }
                // Try the next model
                continue;
            }
        }
        
        // If we get here, all models failed
        if ($this->logger) {
            $this->logger->error('All models failed: ' . $lastError->getMessage());
        }
        
        return null;
    }
} 