<?php

namespace App\Services;

use App\Models\Device;
use GuzzleHttp\Client;
use App\Models\Contact;
use App\Models\MessageHistory;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;

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

    public function checkDevices(string $deviceId, string $token): array
    {
        try {
            $response = $this->client->get($this->apiBaseUrl . '/devices', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            $devices = $result['devices'];
            $isConnected = false;
            $targetDevice = null;

            // Find the specific device in the list
            foreach ($devices as $device) {
                if ($device['deviceId'] === $deviceId) {
                    $isConnected = ($device['status'] === 'connected');
                    $targetDevice = $device;
                    break;
                }
            }

            $device = Device::where('deviceID', $targetDevice['deviceId'])->first();
            $device->update([
                'is_connected' => $isConnected,
            ]);

            if (!$isConnected) {
                return [
                    'success' => false,
                    'isConnected' => false,
                    'message' => 'Device is not connected'
                ];
            }

            return [
                'success' => true,
                'isConnected' => true,
            ];
        } catch (GuzzleException $e) {
            Log::error('Error checking devices: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error checking devices: ' . $e->getMessage()
            ];
        }
    }

    public function sendMessage(string $deviceId, string $to, string $message): array
    {
        try {
            $tokenResult = $this->getAuthToken();
            if (isset($tokenResult['error'])) {
                return $tokenResult;
            }

            $response = $this->client->post($this->apiBaseUrl . '/send', [
                'json' => [
                    'deviceId' => $deviceId,
                    'to' => $to,
                    'message' => $message
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
                Log::error("Failed to parse JSON response: " . json_last_error_msg());
                return ['error' => 'Invalid response format', 'raw_response' => $body];
            }

            // Create message history record
            $this->logMessageHistory($deviceId, $to, $message, $result);

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return [
                'error' => 'Failed to send message: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }

    public function sendBulkMessage(string $deviceId, array $recipients, string $message): array
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $results[$recipient] = $this->sendMessage($deviceId, $recipient, $message);
        }
        return $results;
    }

    public function sendMedia(string $deviceId, string $to, string $caption, $media = null): array
    {
        try {
            $tokenResult = $this->getAuthToken();
            if (isset($tokenResult['error'])) {
                return $tokenResult;
            }

            $multipart = [
                [
                    'name' => 'deviceId',
                    'contents' => $deviceId
                ],
                [
                    'name' => 'to',
                    'contents' => $to
                ],
                [
                    'name' => 'caption',
                    'contents' => $caption
                ],
            ];

            // Add media file if provided
            if ($media !== null) {
                $multipart[] = [
                    'name' => 'media',
                    'contents' => $media,
                    'filename' => 'media'
                ];
            }

            $response = $this->client->post($this->apiBaseUrl . '/send-media', [
                'multipart' => $multipart,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenResult
                ]
            ]);

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse JSON response: " . json_last_error_msg());
                return ['error' => 'Invalid response format', 'raw_response' => $body];
            }

            // Create message history record with media tag
            $this->logMessageHistory($deviceId, $to, $caption . ' [Media Message]', $result);

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error sending media: ' . $e->getMessage());
            return [
                'error' => 'Failed to send media: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }

    private function logMessageHistory(string $deviceId, string $recipient, string $content, array $response): void
    {
        try {
            $device = Device::where('deviceID', $deviceId)->first();
            if (!$device) {
                Log::error("Device not found with ID: {$deviceId}");
                return;
            }

            // Check if there's a contact record for this recipient
            $contactId = null;
            $contact = Contact::where('phone', $recipient)->first();
            if ($contact) {
                $contactId = $contact->id;
            }

            MessageHistory::create([
                'subscription_id' => $device->subscription_id,
                'device_id' => $device->id,
                'contact_id' => $contactId,
                'message' => $content,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log message history: ' . $e->getMessage());
        }
    }

    public function sendMessageToGroup(string $deviceId, string $groupId, string $message): array
    {
        try {
            $tokenResult = $this->getAuthToken();
            if (isset($tokenResult['error'])) {
                return $tokenResult;
            }

            $response = $this->client->post($this->apiBaseUrl . '/groups/send', [
                'json' => [
                    'deviceId' => $deviceId,
                    'groupId' => $groupId,
                    'message' => $message
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
                Log::error("Failed to parse JSON response: " . json_last_error_msg());
                return ['error' => 'Invalid response format', 'raw_response' => $body];
            }

            // Create message history record for group message
            $this->logMessageHistory($deviceId, "group-" . $groupId, $message, $result);

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error sending message to group: ' . $e->getMessage());
            return [
                'error' => 'Failed to send message to group: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }


    public function sendMediaToGroup(string $deviceId, string $groupId, string $caption, $media = null): array
    {
        try {
            $tokenResult = $this->getAuthToken();
            if (isset($tokenResult['error'])) {
                return $tokenResult;
            }

            $multipart = [
                [
                    'name' => 'deviceId',
                    'contents' => $deviceId
                ],
                [
                    'name' => 'groupId',
                    'contents' => $groupId
                ],
                [
                    'name' => 'caption',
                    'contents' => $caption
                ],
            ];

            // Add media file if provided
            if ($media !== null) {
                $multipart[] = [
                    'name' => 'media',
                    'contents' => $media,
                    'filename' => 'media'
                ];
            }

            $response = $this->client->post($this->apiBaseUrl . '/groups/send-media', [
                'multipart' => $multipart,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenResult
                ]
            ]);

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse JSON response: " . json_last_error_msg());
                return ['error' => 'Invalid response format', 'raw_response' => $body];
            }

            // Create message history record for group media message
            $this->logMessageHistory($deviceId, "group-" . $groupId, $caption . ' [Media Message to Group]', $result);

            return $result;
        } catch (GuzzleException $e) {
            Log::error('Error sending media to group: ' . $e->getMessage());
            return [
                'error' => 'Failed to send media to group: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }
}
