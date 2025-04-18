<?php

namespace App\Services;

use App\Models\Subscription;
use App\Repositories\DeviceRepository;
use Illuminate\Support\Carbon;

class DeviceService
{
    protected $deviceRepository;
    protected $waBlastService;

    public function __construct(
        DeviceRepository $deviceRepository,
        WaBlastService $waBlastService
    ) {
        $this->deviceRepository = $deviceRepository;
        $this->waBlastService = $waBlastService;
    }

    public function getPaginatedDevices(int $userId, ?string $search = null, int $perPage = 15)
    {
        return $this->deviceRepository->getPaginatedDevices($userId, $search, $perPage);
    }

    public function getDeviceById(int $id)
    {
        return $this->deviceRepository->findById($id);
    }

    public function createDeviceRegister(int $userId, string $name, string $phone, int $subscriptionId = 1, int $trialDays = 7): array
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $deviceId = $this->deviceRepository->generateUniqueDeviceId($name);

        $device = $this->deviceRepository->create([
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'deviceID' => $deviceId,
            'name' => $name,
            'phone' => $phone,
            'limit' => $subscription->limit,
            'expired_at' => now()->addDays($trialDays),
            'is_connected' => false,
        ]);

        $connectionResult = $this->waBlastService->connectDevice($deviceId);

        return [
            'device' => $device,
            'connection' => $connectionResult
        ];
    }

    public function createDevice(string $name, string $phone)
    {
        $deviceId = $this->deviceRepository->generateUniqueDeviceId($name);

        $device = $this->deviceRepository->create([
            'user_id' => auth()->id(),
            'subscription_id' => null,
            'deviceID' => $deviceId,
            'name' => $name,
            'phone' => $phone,
            'limit' => null,
            'expired_at' => null,
            'is_connected' => false,
        ]);

        $this->waBlastService->connectDevice($deviceId);

        return $device;
    }

    public function getUserDevices(int $userId)
    {
        return $this->deviceRepository->getUserDevices($userId);
    }

    public function updateDevice(int $id, array $data)
    {
        $device = $this->deviceRepository->findById($id);
        if (!$device || $device->user_id !== auth()->id()) {
            return false;
        }

        if (isset($data['phone']) && $data['phone'] !== $device->phone) {
            $data['is_connected'] = false;
            $this->waBlastService->deleteDevice($device->deviceID);

            $data['deviceID'] = $this->deviceRepository->generateUniqueDeviceId($data['name']);
            $this->waBlastService->connectDevice($data['deviceID']);
        }

        return $this->deviceRepository->update($id, $data);
    }

    public function extendDeviceExpiry(int $deviceId, int $days)
    {
        $device = $this->deviceRepository->findById($deviceId);

        if (!$device) {
            return false;
        }

        $newExpiryDate = Carbon::parse($device->expired_at)->addDays($days);

        return $this->deviceRepository->update($deviceId, [
            'expired_at' => $newExpiryDate
        ]);
    }
}
