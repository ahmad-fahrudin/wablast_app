<?php

namespace App\Http\Controllers;

use App\Services\DeviceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Subscription;

class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $devices = $this->deviceService->getPaginatedDevices(
            auth()->id(),
            $search,
            15
        );

        return Inertia::render('device/Index', [
            'devices' => $devices,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('device/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $this->deviceService->createDevice(
            $validated['name'],
            $validated['phone']
        );

        return redirect()->route('devices.index');
    }

    public function edit($id)
    {
        $device = $this->deviceService->getDeviceById($id);

        if (!$device || $device->user_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('device/Edit', ['device' => $device]);
    }

    public function update(Request $request, $id)
    {
        $device = $this->deviceService->getDeviceById($id);

        if (!$device || $device->user_id !== auth()->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $this->deviceService->updateDevice($id, $validated);

        return redirect()->route('devices.index');
    }

    public function qrCode($id)
    {
        $device = $this->deviceService->getDeviceById($id);

        if (!$device || $device->user_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('device/QrCode', [
            'device' => $device,
        ]);
    }
}
