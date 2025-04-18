<?php

namespace App\Http\Controllers;

use App\Services\WaBlastService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WaBlastController extends Controller
{
    protected $waBlastService;
    protected $tamuService;

    public function __construct(
        WaBlastService $waBlastService,
    ) {
        $this->waBlastService = $waBlastService;
    }

    public function generateQR(Request $request)
    {
        $request->validate([
            'deviceId' => 'required|string',
        ]);
        try {
            $response = $this->waBlastService->getQrCode($request->deviceId);

            return response()->json($response);
        } catch (Exception $e) {
            Log::error('Error generating QR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkDeviceStatus(Request $request)
    {
        $request->validate([
            'deviceId' => 'required|string',
        ]);

        try {
            // Get token first
            $tokenResult = $this->waBlastService->getAuthToken();
            if (!$tokenResult) {
                return response()->json($tokenResult);
            }

            $this->waBlastService->checkDevices($request->deviceId, $tokenResult);

            return redirect()->route('devices.index');
        } catch (Exception $e) {
            Log::error('Error checking device status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking device status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'deviceId' => 'required|string',
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $result = $this->waBlastService->sendMessage(
                $request->deviceId,
                $request->to,
                $request->message
            );

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendBulkMessage(Request $request)
    {
        $request->validate([
            'deviceId' => 'required|string',
            'recipients' => 'required|array',
            'recipients.*' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $result = $this->waBlastService->sendBulkMessage(
                $request->deviceId,
                $request->recipients,
                $request->message
            );

            return response()->json([
                'success' => true,
                'results' => $result
            ]);
        } catch (Exception $e) {
            Log::error('Error sending bulk messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending bulk messages: ' . $e->getMessage()
            ], 500);
        }
    }
}
