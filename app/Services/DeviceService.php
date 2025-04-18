<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\DeviceSubscription;
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

    public function getPaginatedSubscribedDevices(int $userId, ?string $search = null, int $perPage = 15)
    {
        return $this->deviceRepository->getPaginatedSubscribedDevices($userId, $search, $perPage);
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

    public function getExpiredDevices(int $userId)
    {
        return $this->deviceRepository->getExpiredDevices($userId);
    }

    public function activateDeviceSubscription(int $deviceId, int $subscriptionId, int $days): bool
    {
        $device = $this->deviceRepository->findById($deviceId);
        $subscription = Subscription::findOrFail($subscriptionId);

        if (!$device || !$subscription) {
            return false;
        }

        $expiryDate = now()->addDays($days);

        DeviceSubscription::updateOrCreate(
            [
                'device_id' => $deviceId,
                'subscription_id' => $subscriptionId,
            ],
            [
                'expired_at' => $expiryDate,
            ]
        );

        // Update the device with subscription details
        return $this->deviceRepository->update($deviceId, [
            'subscription_id' => $subscriptionId,
            'limit' => $subscription->limit,
            'expired_at' => $expiryDate,
        ]);
    }
}
