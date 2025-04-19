<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Device;
use App\Models\Contact;
use App\Models\MessageHistory;
use Illuminate\Support\Facades\Log;
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
        $results = [];
        $hasQuotaExhausted = false;

        if ($recipientType === 'contact') {
            foreach ($recipients as $recipient) {
                $phone = null;
                $name = null;

                // Handle both ID and object formats
                if (is_array($recipient) || is_object($recipient)) {
                    // Check if this is a manually added contact
                    if (isset($recipient['isManual']) && $recipient['isManual']) {
                        $phone = $recipient['phone'];
                        $name = isset($recipient['name']) ? $recipient['name'] : $phone;
                    } else if (isset($recipient['phone'])) {
                        $phone = $recipient['phone'];
                        $name = isset($recipient['name']) ? $recipient['name'] : 'Contact';
                    } else if (isset($recipient['id'])) {
                        $contact = Contact::find($recipient['id']);
                        if ($contact) {
                            $phone = $contact->phone;
                            $name = $contact->name;
                        }
                    }
                } else {
                    // Regular contact ID
                    $contact = Contact::find($recipient);
                    if ($contact) {
                        $phone = $contact->phone;
                        $name = $contact->name;
                    }
                }

                if ($phone) {
                    $result = $this->wablastService->sendMessage($deviceId, $phone, $message);

                    if (isset($result['quotaExhausted']) && $result['quotaExhausted']) {
                        $hasQuotaExhausted = true;
                    }

                    $results[$phone] = $result;

                    // Save message history if successful
                    if (isset($result['success']) && $result['success']) {
                        $this->logMessageHistory($deviceId, $phone, $message, 'contact', $name);
                    }
                }
            }
        } elseif ($recipientType === 'group') {
            foreach ($recipients as $recipient) {
                $groupID = null;
                $groupName = null;

                // Handle both ID and object formats
                if (is_array($recipient) || is_object($recipient)) {
                    if (isset($recipient['id'])) {
                        $group = Group::find($recipient['id']);
                        if ($group && $group->groupID) {
                            $groupID = $group->groupID;
                            $groupName = $group->subject;
                        }
                    }
                } else {
                    // Regular group ID
                    $group = Group::find($recipient);
                    if ($group && $group->groupID) {
                        $groupID = $group->groupID;
                        $groupName = $group->subject;
                    }
                }

                if ($groupID) {
                    $result = $this->wablastService->sendMessageToGroup($deviceId, $groupID, $message);

                    if (isset($result['quotaExhausted']) && $result['quotaExhausted']) {
                        $hasQuotaExhausted = true;
                    }

                    $results[$groupID] = $result;

                    // Save message history if successful
                    if (isset($result['success']) && $result['success']) {
                        $this->logMessageHistory($deviceId, $groupID, $message, 'group', $groupName);
                    }
                }
            }
        } elseif ($recipientType === 'broadcast') {
            foreach ($recipients as $recipient) {
                $groupId = null;

                // Handle both ID and object formats for broadcast groups
                if (is_array($recipient) || is_object($recipient)) {
                    if (isset($recipient['id'])) {
                        $groupId = $recipient['id'];
                    } else {
                        $groupId = $recipient;
                    }
                } else {
                    $groupId = $recipient;
                }

                $group = Group::with('contacts')->find($groupId);
                if ($group) {
                    foreach ($group->contacts as $contact) {
                        $result = $this->wablastService->sendMessage($deviceId, $contact->phone, $message);

                        if (isset($result['quotaExhausted']) && $result['quotaExhausted']) {
                            $hasQuotaExhausted = true;
                            // Break out of the loop if quota is exhausted
                            break 2;
                        }

                        $results[$contact->phone] = $result;

                        // Save message history if successful
                        if (isset($result['success']) && $result['success']) {
                            $this->logMessageHistory($deviceId, $contact->phone, $message, 'contact', $contact->name);
                        }
                    }
                }
            }
        }

        $response = [
            'success' => !empty($results) && !$hasQuotaExhausted,
            'results' => $results,
            'message' => !empty($results) ? 'Messages sent successfully' : 'No messages were sent'
        ];

        // Add quota exhausted flag if needed
        if ($hasQuotaExhausted) {
            $response['quotaExhausted'] = true;
            $response['message'] = 'Message quota exhausted. Please purchase additional quota to continue sending messages.';
        }

        return $response;
    }

    public function sendMediaMessage($deviceId, $recipientType, $recipients, $caption, $media)
    {
        $results = [];
        $hasQuotaExhausted = false;

        $device = Device::where('deviceID', $deviceId)->first();

        // First check if quota is available
        if ($device && $device->quota <= 0) {
            return [
                'success' => false,
                'quotaExhausted' => true,
                'message' => 'Message quota exhausted. Please purchase additional quota to continue sending messages.'
            ];
        }

        if ($recipientType === 'contact') {
            foreach ($recipients as $recipient) {
                $phone = null;
                $name = null;

                // Handle both ID and object formats
                if (is_array($recipient) || is_object($recipient)) {
                    // Check if this is a manually added contact
                    if (isset($recipient['isManual']) && $recipient['isManual']) {
                        $phone = $recipient['phone'];
                        $name = isset($recipient['name']) ? $recipient['name'] : $phone;
                    } else if (isset($recipient['phone'])) {
                        $phone = $recipient['phone'];
                        $name = isset($recipient['name']) ? $recipient['name'] : 'Contact';
                    } else if (isset($recipient['id'])) {
                        $contact = Contact::find($recipient['id']);
                        if ($contact) {
                            $phone = $contact->phone;
                            $name = $contact->name;
                        }
                    }
                } else {
                    // Regular contact ID
                    $contact = Contact::find($recipient);
                    if ($contact) {
                        $phone = $contact->phone;
                        $name = $contact->name;
                    }
                }

                if ($phone) {
                    $result = $this->wablastService->sendMedia($deviceId, $phone, $caption, $media);

                    if (isset($result['quotaExhausted']) && $result['quotaExhausted']) {
                        $hasQuotaExhausted = true;
                    }

                    $results[$phone] = $result;

                    // Save message history if successful
                    if (isset($result['success']) && $result['success']) {
                        $this->logMessageHistory($deviceId, $phone, $caption . ' [Media Message]', 'contact', $name);
                    }
                }
            }
        } elseif ($recipientType === 'group') {
            foreach ($recipients as $recipient) {
                $groupID = null;
                $groupName = null;

                // Handle both ID and object formats
                if (is_array($recipient) || is_object($recipient)) {
                    if (isset($recipient['id'])) {
                        $group = Group::find($recipient['id']);
                        if ($group && $group->groupID) {
                            $groupID = $group->groupID;
                            $groupName = $group->subject;
                        }
                    }
                } else {
                    // Regular group ID
                    $group = Group::find($recipient);
                    if ($group && $group->groupID) {
                        $groupID = $group->groupID;
                        $groupName = $group->subject;
                    }
                }

                if ($groupID) {
                    $result = $this->wablastService->sendMediaToGroup($deviceId, $groupID, $caption, $media);

                    if (isset($result['quotaExhausted']) && $result['quotaExhausted']) {
                        $hasQuotaExhausted = true;
                    }

                    $results[$groupID] = $result;

                    // Save message history if successful
                    if (isset($result['success']) && $result['success']) {
                        $this->logMessageHistory($deviceId, $groupID, $caption . ' [Media Message]', 'group', $groupName);
                    }
                }
            }
        } elseif ($recipientType === 'broadcast') {
            foreach ($recipients as $recipient) {
                $groupId = null;

                // Handle both ID and object formats for broadcast groups
                if (is_array($recipient) || is_object($recipient)) {
                    if (isset($recipient['id'])) {
                        $groupId = $recipient['id'];
                    } else {
                        $groupId = $recipient;
                    }
                } else {
                    $groupId = $recipient;
                }

                $group = Group::with('contacts')->find($groupId);
                if ($group) {
                    foreach ($group->contacts as $contact) {
                        $result = $this->wablastService->sendMedia($deviceId, $contact->phone, $caption, $media);

                        if (isset($result['quotaExhausted']) && $result['quotaExhausted']) {
                            $hasQuotaExhausted = true;
                            // Break out of the loop if quota is exhausted
                            break 2;
                        }

                        $results[$contact->phone] = $result;

                        // Save message history if successful
                        if (isset($result['success']) && $result['success']) {
                            $this->logMessageHistory($deviceId, $contact->phone, $caption . ' [Media Message]', 'contact', $contact->name);
                        }
                    }
                }
            }
        }

        $response = [
            'success' => !empty($results) && !$hasQuotaExhausted,
            'results' => $results,
            'message' => !empty($results) ? 'Media messages sent successfully' : 'No media messages were sent'
        ];

        // Add quota exhausted flag if needed
        if ($hasQuotaExhausted) {
            $response['quotaExhausted'] = true;
            $response['message'] = 'Message quota exhausted. Please purchase additional quota to continue sending messages.';
        }

        return $response;
    }

    /**
     * Log a message in the message history
     */
    protected function logMessageHistory(string $deviceId, string $recipient, string $content, string $recipientType, ?string $recipientName = null): void
    {
        try {
            $device = Device::where('deviceID', $deviceId)->first();
            if (!$device) {
                Log::error("Failed to log message history: Device with deviceID {$deviceId} not found");
                return;
            }

            // For group messages, store with group_id
            if ($recipientType === 'group') {
                try {
                    // Extract group ID from the recipient format
                    $groupId = str_replace('@g.us', '', $recipient);

                    // Check if the group exists in the database
                    $group = Group::where('groupID', $groupId)->first();

                    MessageHistory::create([
                        'subscription_id' => $device->subscription_id,
                        'device_id' => $device->id,
                        'contact_id' => null,
                        'group_id' => $group ? $group->id : null,
                        'message' => $content,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to save group message history: ' . $e->getMessage());
                }
                return;
            }

            // For contact messages
            // Clean up phone number
            $phoneNumber = preg_replace('/@[gs]\.(?:whatsapp\.net|us)$/', '', $recipient);

            // Check if there's a contact record for this recipient
            $contact = Contact::where('phone', $phoneNumber)->first();

            MessageHistory::create([
                'subscription_id' => $device->subscription_id,
                'device_id' => $device->id,
                'contact_id' => $contact ? $contact->id : null,
                'group_id' => null,
                'message' => $content,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log message history: ' . $e->getMessage());
        }
    }
}
