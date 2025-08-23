<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class SiamGpsController extends Controller
{
    /**
     * Get route history from Siam GPS API
     */
    public function getRouteHistory(Request $request): JsonResponse
    {
        $request->validate([
            'vehicleId' => 'required|integer',
            'startDate' => 'required|string',
            'endDate' => 'required|string',
            'authorization' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => $request->authorization,
                'Accept' => 'application/json',
            ])->get('https://services.siamgpstrack.com/playback/listByVehicleId', [
                'vehicleId' => $request->vehicleId,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Siam GPS API error',
                    'status' => $response->status(),
                    'message' => $response->body()
                ], $response->status());
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch from Siam GPS',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get realtime vehicle data from Siam GPS API
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        $request->validate([
            'vehicleId' => 'required|integer',
            'authorization' => 'required|string',
        ]);

        try {
            // Adjust URL based on actual Siam GPS realtime API endpoint
            $response = Http::withHeaders([
                'Authorization' => $request->authorization,
                'Accept' => 'application/json',
            ])->get("https://services.siamgpstrack.com/vehicle/current/{$request->vehicleId}");

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Siam GPS API error',
                    'status' => $response->status(),
                    'message' => $response->body()
                ], $response->status());
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch from Siam GPS',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get realtime vehicle data using the new endpoint format
     */
    public function getRealtimeDataByVehicleId(Request $request, int $vehicleId): JsonResponse
    {
        $request->validate([
            'authorization' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => $request->authorization,
                'Accept' => 'application/json',
            ])->get("https://services.siamgpstrack.com/realtime/listByVehicleId/{$vehicleId}");

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Siam GPS API error',
                    'status' => $response->status(),
                    'message' => $response->body()
                ], $response->status());
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch from Siam GPS',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}