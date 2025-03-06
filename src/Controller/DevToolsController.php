<?php

namespace App\Controller;

use App\Service\ToxicityDetector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/dev', name: 'app_dev_')]
class DevToolsController extends AbstractController
{
    #[Route('/test-api', name: 'test_api')]
    public function testApi(ToxicityDetector $toxicityDetector, LoggerInterface $logger): JsonResponse
    {
        // Test connection
        $connectionTest = $toxicityDetector->testConnection();
        
        // Get models if connected
        $models = [];
        if ($connectionTest) {
            $models = $toxicityDetector->getAvailableModels();
        }
        
        // Return results
        return $this->json([
            'connection_test' => $connectionTest,
            'available_models' => $models,
            'environment' => $_ENV['APP_ENV'],
            'api_key_prefix' => substr($_ENV['OPENAI_API_KEY'] ?? 'not-set', 0, 5) . '...',
        ]);
    }
} 