<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use App\Services\DeviceService;
use App\Services\GroupService;
use App\Services\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class MessageController extends Controller
{
    protected $messageService;
    protected $deviceService;
    protected $groupService;
    protected $contactService;

    public function __construct(
        MessageService $messageService,
        DeviceService $deviceService,
        GroupService $groupService,
        ContactService $contactService
    ) {
        $this->messageService = $messageService;
        $this->deviceService = $deviceService;
        $this->groupService = $groupService;
        $this->contactService = $contactService;
    }

    public function messagePage()
    {
        $devices = $this->deviceService->getAllDevices();
        // Load only needed contacts and groups for initial rendering
        // Detailed contact data will be loaded via API with pagination
        $contacts = $this->contactService->getPaginatedContacts(
            auth()->id(),
            null,
            10 // Initial small batch
        );
        $groups = $this->groupService->getAllGroups();

        return inertia('message/MessagePage', [
            'devices' => $devices,
            'contacts' => $contacts,
            'groups' => $groups
        ]);
    }

    public function mediaPage()
    {
        $devices = $this->deviceService->getAllDevices();
        // Load only needed contacts and groups for initial rendering
        // Detailed contact data will be loaded via API with pagination
        $contacts = $this->contactService->getPaginatedContacts(
            auth()->id(),
            null,
            10 // Initial small batch
        );
        $groups = $this->groupService->getAllGroups();

        return inertia('message/MediaPage', [
            'devices' => $devices,
            'contacts' => $contacts,
            'groups' => $groups
        ]);
    }

    // API endpoint untuk mendapatkan kontak dengan paginasi
    public function getContactsForSelector(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $contacts = $this->contactService->getPaginatedContacts(
            auth()->id(),
            $search,
            $perPage,
            $page
        );

        return response()->json($contacts);
    }

    public function sendTextMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceId' => 'required|string',
            'recipientType' => 'required|string|in:contact,group,broadcast',
            'recipients' => 'required|array|min:1',
            'message' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->messageService->sendTextMessage(
            $request->deviceId,
            $request->recipientType,
            $request->recipients,
            $request->message
        );

        return response()->json($result);
    }

    public function sendMediaMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deviceId' => 'required|string',
            'recipientType' => 'required|string|in:contact,group,broadcast',
            'recipients' => 'required|array|min:1',
            'caption' => 'nullable|string',
            'media' => 'required|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $mediaFile = $request->file('media');

        $result = $this->messageService->sendMediaMessage(
            $request->deviceId,
            $request->recipientType,
            $request->recipients,
            $request->caption ?? '',
            $mediaFile
        );

        return response()->json($result);
    }
}
