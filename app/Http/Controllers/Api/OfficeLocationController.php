<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeLocationResource;
use App\Models\OfficeLocation;
use Illuminate\Http\JsonResponse;

class OfficeLocationController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = OfficeLocation::query()
            ->active()
            ->apiPublic()
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $locations
                ->map(fn (OfficeLocation $location) => OfficeLocationResource::make($location)->resolve())
                ->values(),
        ]);
    }
}
