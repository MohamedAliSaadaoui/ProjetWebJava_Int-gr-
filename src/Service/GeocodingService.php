<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private $httpClient;
    private $mapboxAccessToken;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->mapboxAccessToken = 'pk.eyJ1Ijoic2FycmFtZXNzMSIsImEiOiJjbTdrcnlhbmEwM28zMmpzNmExcGR1eXp5In0.8O_U8p7Jkxoe5cd_QQe81g';
    }

    public function getCoordinates(string $location): ?array
    {
        $url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . urlencode($location) . '.json?access_token=' . $this->mapboxAccessToken;
        
        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            if (isset($data['features'][0])) {
                $coordinates = $data['features'][0]['geometry']['coordinates'];
                return [
                    'longitude' => $coordinates[0],
                    'latitude' => $coordinates[1],
                ];
            }
        } catch (\Exception $e) {
            // Log l'erreur ou gérez-la comme vous le souhaitez
            // Pour le débogage, vous pouvez utiliser :
            // dump($e->getMessage());
        }

        return null;
    }
}