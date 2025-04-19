<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Group;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

class MessageService
{
    protected $wablastService;
    protected $deviceService;

    public function __construct(WaBlastService $wablastService, DeviceService $deviceService)
    {
        $this->wablastService = $wablastService;
        $this->deviceService = $deviceService;
    }

    public function sendTextMessage($deviceId, $recipientType, $recipients, $message)
    {
        // Validate if device exists and is connected
        $device = Device::where('deviceID', $deviceId)->first();
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        // Check if device is connected
        $tokenResult = $this->wablastService->getAuthToken();
        $deviceStatus = $this->wablastService->checkDevices($deviceId, $tokenResult);
        if (!$deviceStatus['isConnected']) {
            return ['success' => false, 'message' => 'Device is not connected'];
        }

        $results = [];

        if ($recipientType === 'contact') {
            // Send to individual contacts
            foreach ($recipients as $contactId) {
                $contact = Contact::find($contactId);
                if ($contact) {
                    $result = $this->wablastService->sendMessage($deviceId, $contact->phone, $message);
                    $results[$contact->phone] = $result;
                }
            }
        } elseif ($recipientType === 'group') {
            // Send to WhatsApp groups
            foreach ($recipients as $groupId) {
                $group = Group::find($groupId);
                if ($group && $group->groupID) {
                    $result = $this->wablastService->sendMessageToGroup($deviceId, $group->groupID, $message);
                    $results[$group->subject] = $result;
                }
            }
        } elseif ($recipientType === 'broadcast') {
            // Send to contacts in a custom group (broadcast)
            foreach ($recipients as $groupId) {
                $group = Group::with('contacts')->find($groupId);
                if ($group) {
                    foreach ($group->contacts as $contact) {
                        $result = $this->wablastService->sendMessage($deviceId, $contact->phone, $message);
                        $results[$contact->phone] = $result;
                    }
                }
            }
        }

        return [
            'success' => !empty($results),
            'results' => $results,
            'message' => !empty($results) ? 'Messages sent successfully' : 'No messages were sent'
        ];
    }

    public function sendMediaMessage($deviceId, $recipientType, $recipients, $caption, $media)
    {
        // Validate if device exists and is connected
        $device = Device::where('deviceID', $deviceId)->first();
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }

        // Check if device is connected
        $tokenResult = $this->wablastService->getAuthToken();
        $deviceStatus = $this->wablastService->checkDevices($deviceId, $tokenResult);
        if (!$deviceStatus['isConnected']) {
            return ['success' => false, 'message' => 'Device is not connected'];
        }

        $results = [];

        if ($recipientType === 'contact') {
            // Send to individual contacts
            foreach ($recipients as $contactId) {
                $contact = Contact::find($contactId);
                if ($contact) {
                    $result = $this->wablastService->sendMedia($deviceId, $contact->phone, $caption, $media);
                    $results[$contact->phone] = $result;
                }
            }
        } elseif ($recipientType === 'group') {
            // Send to WhatsApp groups
            foreach ($recipients as $groupId) {
                $group = Group::find($groupId);
                if ($group && $group->groupID) {
                    $result = $this->wablastService->sendMediaToGroup($deviceId, $group->groupID, $caption, $media);
                    $results[$group->subject] = $result;
                }
            }
        } elseif ($recipientType === 'broadcast') {
            // Send to contacts in a custom group (broadcast)
            foreach ($recipients as $groupId) {
                $group = Group::with('contacts')->find($groupId);
                if ($group) {
                    foreach ($group->contacts as $contact) {
                        $result = $this->wablastService->sendMedia($deviceId, $contact->phone, $caption, $media);
                        $results[$contact->phone] = $result;
                    }
                }
            }
        }

        return [
            'success' => !empty($results),
            'results' => $results,
            'message' => !empty($results) ? 'Media messages sent successfully' : 'No media messages were sent'
        ];
    }

    public function getUserDevices()
    {
        $userId = Auth::id();
        return $this->deviceService->getDevicesByUserId($userId);
    }
}
