<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'user' => UserProfileResource::make(
                $this->hydrateUserForResponse($request->user())
            )->resolve(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'sometimes|string|nullable|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'zip_code' => 'sometimes|string|max:20',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'user' => UserProfileResource::make(
                $this->hydrateUserForResponse(
                    User::query()
                        ->apiProfile()
                        ->whereKey($request->user()->id)
                        ->firstOrFail()
                )
            )->resolve(),
        ]);
    }

    private function hydrateUserForResponse(User $user): User
    {
        $relations = [];

        if (Schema::hasTable('tribes')) {
            $relations['tribe'] = fn ($query) => $query->apiPublic();
        }

        if (Schema::hasTable('notification_preferences')) {
            $relations['notificationPreferences'] = fn ($query) => $query->apiSelect();
        }

        if ($relations === []) {
            return $user;
        }

        return $user->loadMissing($relations);
    }
}
