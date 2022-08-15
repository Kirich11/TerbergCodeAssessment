<?php

namespace App\Service;

use GuzzleHttp\Client;

class MercedezClient
{
    private const MARKET_URI = '/markets';
    private string $apiKey;

    public function __construct(string $baseUri, string $apiKey)
    {
        $this->baseUri = $baseUri;
        $this->apiKey  = $apiKey;
    }

    public function getCars(?string $lang = null, ?string $country = null)
    {
        $queryParams = [
            'apikey' => $this->apiKey,
            'fieldsFilter' => ['_links']
        ];

        if ($lang !== null) {
            $queryParams['language'] = $lang;
        }

        if ($country !== null) {
            $queryParams['country'] = $country;
        }

        $client         = new Client();
        $marketResponseBody = $client->request(
            'GET',
            $this->baseUri.self::MARKET_URI,
            [
                'query' => $queryParams
            ]
        )->getBody();
        $marketContents = json_decode($marketResponseBody->getContents(), true);
        if (count($marketContents) === 0) {
            return [];
        }

        $modelsLink = $marketContents[0]['_links']['models'];
        
        return json_decode($client->request('GET', $modelsLink)->getBody()->getContents(), true);
    }
}