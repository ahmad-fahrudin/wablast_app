<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class WaBlastService
{
    private $client;
    private $apiBaseUrl;
    private $secretKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiBaseUrl = 'http://localhost:10020/api/whatsapp';
        $this->secretKey = env('WABLAST_SECRET_KEY', 'your_secret_key');
    }

    public function getAuthToken()
    {
        try {
            $response = $this->client->post($this->apiBaseUrl . '/auth/generate', [
                'json' => [
                    'token' => $this->secretKey
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            $token = $result['token'] ?? null;
            if (!$token) {
                return ['error' => 'No token received from authentication service'];
            }

            return $token;
        } catch (GuzzleException $e) {
            Log::error('Error generating token: ' . $e->getMessage());
            return ['error' => 'Failed to generate token: ' . $e->getMessage()];
        }
    }

    public function connectDevice(string $deviceId)
    {
        try {
            $tokenResult = $this->getAuthToken();

            $response = $this->client->post($this->apiBaseUrl . '/connect', [
                'json' => [
                    'deviceId' => $deviceId
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenResult
                ]
            ]);

            $body = $response->getBody()->getContents();

            $result = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse JSON: " . json_last_error_msg());
                return ['error' => $body];
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error connecting device: ' . $e->getMessage());
            return ['error' => 'Failed to connect device: ' . $e->getMessage()];
        }
    }

    public function getQrCode(string $deviceId)
    {
        $tokenResult = $this->getAuthToken();
        if (!$tokenResult) {
            return $tokenResult;
        }

        try {
            $response = $this->client->get($this->apiBaseUrl . '/qr/' . $deviceId, [
                'headers' => [
                    'Accept' => 'image/png',
                    'Authorization' => 'Bearer ' . $tokenResult
                ],
                'timeout' => 30
            ]);

            $contentType = $response->getHeaderLine('Content-Type');

            // For Postman testing, we need to return the image as base64
            if (strpos($contentType, 'image/png') !== false) {
                $imageData = $response->getBody()->getContents();
                $base64Image = base64_encode($imageData);

                return $base64Image;
            }

            $body = $response->getBody()->getContents();

            $result = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $body;
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error getting QR code: ' . $e->getMessage());
            return ['error' => 'Failed to get QR code: ' . $e->getMessage()];
        }
    }

    public function deleteDevice(string $deviceId)
    {
        try {
            $tokenResult = $this->getAuthToken();
            if (isset($tokenResult['error'])) {
                return $tokenResult;
            }

            $response = $this->client->delete($this->apiBaseUrl . '/delete', [
                'json' => [
                    'deviceId' => $deviceId
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenResult
                ]
            ]);

            $body = $response->getBody()->getContents();

            $result = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse JSON: " . json_last_error_msg());
                return ['error' => $body];
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error deleting device: ' . $e->getMessage());
            return ['error' => 'Failed to delete device: ' . $e->getMessage()];
        }
    }
}
