<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = [
            'applications_by_status' => Application::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get(),
            'pending_applications' => Application::where('status', 'submitted')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'expiring_soon' => Vehicle::expiringSoon(30)->count(),
        ];

        return response()->json($stats);
    }
}
