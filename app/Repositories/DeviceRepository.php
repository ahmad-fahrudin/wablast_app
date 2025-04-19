<?php

namespace App\Repositories;

use App\Models\Device;
use Illuminate\Support\Str;

class DeviceRepository
{
    public function getPaginatedDevices(int $userId, ?string $search = null, int $perPage = 15)
    {
        $query = Device::where('user_id', $userId)
            ->with('subscription');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('deviceID', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function getAllDevices()
    {
        return Device::all();
    }
    public function getPaginatedSubscribedDevices(int $userId, ?string $search = null, int $perPage = 15)
    {
        $query = Device::where('user_id', $userId)
            ->with('subscription');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('deviceID', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Device
    {
        return Device::create($data);
    }

    public function findById(int $id): ?Device
    {
        return Device::find($id);
    }

    public function findByDeviceId(string $deviceId): ?Device
    {
        return Device::where('deviceID', $deviceId)->first();
    }

    public function findByUserId(int $userId): ?Device
    {
        return Device::where('user_id', $userId)->first();
    }

    public function getUserDevices(int $userId)
    {
        return Device::where('user_id', $userId)->get();
    }

    public function update(int $id, array $data): bool
    {
        return Device::where('id', $id)->update($data);
    }

    public function updateConnectionStatus(string $deviceId, bool $status): bool
    {
        return Device::where('deviceID', $deviceId)->update(['is_connected' => $status]);
    }

    public function generateUniqueDeviceId(string $name): string
    {
        $baseDeviceId = Str::slug($name, '');
        $randomString = Str::upper(Str::random(4));
        return $baseDeviceId . $randomString;
    }

    public function getExpiredDevices(int $userId)
    {
        return Device::where('user_id', $userId)
            ->where(function ($query) {
                $query->whereDoesntHave('subscription')
                    ->orWhereHas('subscription', function ($q) {
                        $q->whereNull('expired_at')
                            ->orWhere('expired_at', '<', now());
                    });
            })
            ->get();
    }
}
