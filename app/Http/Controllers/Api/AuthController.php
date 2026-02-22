<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tribe_id' => 'required|exists:tribes,id',
            'tribal_enrollment_id' => 'required|string|max:255|unique:users,tribal_enrollment_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:50',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
        ]);

        $validated['email'] = Str::lower(trim($validated['email']));
        $validated['tribal_enrollment_id'] = trim($validated['tribal_enrollment_id']);

        $user = User::create([
            ...$validated,
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'password' => Hash::make($validated['password']),
        ]);

        try {
            $token = $this->issueApiToken($user);
        } catch (Throwable $exception) {
            Log::error('Failed to issue registration API token.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication service is not ready. Please contact support.',
            ], 503);
        }

        return response()->json([
            'user' => UserProfileResource::make(
                $this->hydrateUserForResponse($user)
            )->resolve(),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $identifier = trim(Str::lower($validated['email']));

        $user = User::query()
            ->apiAuth()
            ->whereRaw('LOWER(email) = ?', [$identifier])
            ->first();

        if (! $user) {
            $user = User::query()
                ->apiAuth()
                ->whereRaw('LOWER(tribal_enrollment_id) = ?', [$identifier])
                ->first();
        }

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Account is inactive',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);

        try {
            $token = $this->issueApiToken($user);
        } catch (Throwable $exception) {
            Log::error('Failed to issue login API token.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication service is not ready. Please contact support.',
            ], 503);
        }

        return response()->json([
            'user' => UserProfileResource::make(
                $this->hydrateUserForResponse($user)
            )->resolve(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json([
            'message' => 'Password reset link sent to your email',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json([
            'message' => 'Password reset successfully',
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

    private function issueApiToken(User $user): string
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            throw new \RuntimeException('personal_access_tokens table is missing.');
        }

        return $user->createToken('auth-token')->plainTextToken;
    }
}
