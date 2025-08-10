<?php

namespace alttextlab\AltTextLab\services;

use alttextlab\AltTextLab\AltTextLab;
use Craft;
use Exception;

class ApiService
{

    private $BASE_URL = 'https://app.alttextlab.com/api/';

    public function GetAccount()
    {
        try {
            $api_version = 'v1';

            $settings = AltTextLab::getInstance()->getSettings();
            $apiKey = $settings->getApiKey(true);

            $client = Craft::createGuzzleClient();

            $response = $client->get($this->BASE_URL . $api_version . '/subscriptions/info', [
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();

            if (substr((string)$statusCode, 0, 1) !== '2') {
                return false;
            }

            $response = json_decode($response->getBody()->getContents(), true);

            $account = array(
                'plan' => $response['planName'] ?: null,
                'isActive' => $response['isActive'] ?: false,
                'credits' => $response['credits'] ?: 0,
                'nextReceiving' => $response['nextReceiving'] ?: null,
                'nextReceivingAt' => $response['nextReceivingAt'] ?: null,
            );

            return $account;
        } catch (Exception $e) {
            Craft::error('Get api key error: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    public function generateAltText(array $callDetailsArray): string
    {
        $api_version = 'v1';
        $requestJSON = json_encode($callDetailsArray);

        try {
            $settings = AltTextLab::getInstance()->getSettings();
            $apiKey = $settings->getApiKey(true);

            $client = Craft::createGuzzleClient();

            $response = $client->post($this->BASE_URL . $api_version . '/alt-text/generate', [
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => $requestJSON,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            $responseBodyJson = json_decode($responseBody, true);


            if ($statusCode == 401) {
                return 'API_KEY_IS_INVALID';
            }

            if ($statusCode == 403 && isset($responseBodyJson['key']) && $responseBodyJson['key'] === 'NOT_ENOUGH_FUNDS') {
                return 'NOT_ENOUGH_FUNDS';
            }

            return $responseBody;

        } catch (Exception $e) {
            Craft::error('Alt text generation error: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}