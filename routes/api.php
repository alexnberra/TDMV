<?php

use App\Http\Controllers\Api\Admin\AdminApplicationController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminDocumentController;
use App\Http\Controllers\Api\Admin\Phase3AutomationController;
use App\Http\Controllers\Api\Admin\Phase3InsightsController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AssistantController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessAccountController;
use App\Http\Controllers\Api\DisabilityPlacardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EmissionsTestController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FleetVehicleController;
use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\InsurancePolicyController;
use App\Http\Controllers\Api\MemberBenefitController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfficeLocationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleInspectionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/office-locations', [OfficeLocationController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'index']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);

    Route::apiResource('vehicles', VehicleController::class);
    Route::get('/vehicles/{vehicle}/renewal-history', [VehicleController::class, 'renewalHistory']);

    Route::apiResource('applications', ApplicationController::class);
    Route::post('/applications/{application}/submit', [ApplicationController::class, 'submit']);
    Route::get('/applications/{application}/timeline', [ApplicationController::class, 'timeline']);
    Route::post('/applications/{application}/cancel', [ApplicationController::class, 'cancel']);

    Route::post('/applications/{application}/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);

    Route::post('/applications/{application}/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [PaymentController::class, 'show']);

    Route::apiResource('households', HouseholdController::class);
    Route::post('/households/{household}/members', [HouseholdController::class, 'addMember']);
    Route::delete('/households/{household}/members/{householdMember}', [HouseholdController::class, 'removeMember']);

    Route::apiResource('appointments', AppointmentController::class);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

    Route::get('/business-accounts', [BusinessAccountController::class, 'index']);
    Route::post('/business-accounts', [BusinessAccountController::class, 'store']);
    Route::get('/business-accounts/{businessAccount}', [BusinessAccountController::class, 'show']);
    Route::put('/business-accounts/{businessAccount}', [BusinessAccountController::class, 'update']);
    Route::delete('/business-accounts/{businessAccount}', [BusinessAccountController::class, 'destroy']);
    Route::post('/business-accounts/{businessAccount}/members', [BusinessAccountController::class, 'addMember']);
    Route::post('/business-accounts/{businessAccount}/fleet-vehicles', [FleetVehicleController::class, 'store']);
    Route::delete('/business-accounts/{businessAccount}/fleet-vehicles/{fleetVehicle}', [FleetVehicleController::class, 'destroy']);

    Route::get('/insurance-policies', [InsurancePolicyController::class, 'index']);
    Route::post('/insurance-policies', [InsurancePolicyController::class, 'store']);
    Route::get('/insurance-policies/{insurancePolicy}', [InsurancePolicyController::class, 'show']);
    Route::put('/insurance-policies/{insurancePolicy}', [InsurancePolicyController::class, 'update']);

    Route::get('/emissions-tests', [EmissionsTestController::class, 'index']);
    Route::post('/emissions-tests', [EmissionsTestController::class, 'store']);
    Route::get('/emissions-tests/{emissionsTest}', [EmissionsTestController::class, 'show']);

    Route::get('/vehicle-inspections', [VehicleInspectionController::class, 'index']);
    Route::post('/vehicle-inspections', [VehicleInspectionController::class, 'store']);
    Route::get('/vehicle-inspections/{vehicleInspection}', [VehicleInspectionController::class, 'show']);

    Route::get('/member-benefits', [MemberBenefitController::class, 'index']);
    Route::post('/member-benefits', [MemberBenefitController::class, 'store']);
    Route::get('/member-benefits/{memberBenefit}', [MemberBenefitController::class, 'show']);
    Route::put('/member-benefits/{memberBenefit}', [MemberBenefitController::class, 'update']);

    Route::get('/disability-placards', [DisabilityPlacardController::class, 'index']);
    Route::post('/disability-placards', [DisabilityPlacardController::class, 'store']);
    Route::get('/disability-placards/{disabilityPlacard}', [DisabilityPlacardController::class, 'show']);
    Route::put('/disability-placards/{disabilityPlacard}', [DisabilityPlacardController::class, 'update']);
    Route::delete('/disability-placards/{disabilityPlacard}', [DisabilityPlacardController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notification-preferences', [NotificationController::class, 'preferences']);
    Route::put('/notification-preferences', [NotificationController::class, 'updatePreferences']);

    Route::post('/assistant/query', [AssistantController::class, 'query']);

    Route::middleware(['role:staff,admin'])->prefix('admin')->group(function (): void {
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/applications', [AdminApplicationController::class, 'index']);
        Route::put('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus']);
        Route::post('/applications/{application}/request-info', [AdminApplicationController::class, 'requestMoreInfo']);
        Route::put('/documents/{document}/review', [AdminDocumentController::class, 'review']);
        Route::get('/phase3/insights', [Phase3InsightsController::class, 'stats']);
        Route::post('/phase3/automation/run', [Phase3AutomationController::class, 'run']);
    });
});
