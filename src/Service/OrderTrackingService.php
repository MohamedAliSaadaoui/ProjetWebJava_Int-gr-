<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderTrackingService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getTrackingLocation(string $trackingNumber): array
    {
        // Using environment variable for API key
        $apiUrl = 'https://api.openrouteservice.org/geocode/search';
        
        // Add the API key and tracking number as query parameters
        $response = $this->httpClient->request('GET', $apiUrl, [
            'query' => [
                'tracking_number' => $trackingNumber,
                'api_key' => $this->apiKey
            ]
        ]);

        // Handle possible errors
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to retrieve tracking information');
        }

        $data = $response->toArray();

        // Return the location or a default value if not found
        return [
            'lat' => $data['current_location']['lat'] ?? 36.8065,
            'lng' => $data['current_location']['lng'] ?? 10.1815
        ];
    }
}