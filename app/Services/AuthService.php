<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Events\UserCreatedByAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{

    public function __construct(private readonly UserService $userService) {}

    public function register(array $data): array
    {
        $user = $this->userService->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::Student->value,
            'status' => $data['status'] ?? null,
            'profile' => $data['profile'] ?? [],
        ]);

        return [
            'user' => $user,
            'token' => $this->issueToken($user),
        ];
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password ?? '')) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($user->password)) {
            throw ValidationException::withMessages([
                'email' => ['We have sent you a set password link. Please check your email to set your password.'],
            ]);
        }

        return [
            'user' => $user,
            'token' => $this->issueToken($user),
        ];
    }

    public function logout(User $user): void
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        $token->delete();
    }

    public function refresh(User $user): array
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        $token->delete();

        return ['token' => $this->issueToken($user)];
    }

    private function issueToken(User $user): array
    {
        $accessToken = $user->createToken(
            'access-token',
            ['access-api'],
            now()->addDays(1)
        )->plainTextToken;

        $refreshToken = $user->createToken(
            'refresh-token',
            ['issue-access-token'],
            now()->addDays(30)
        )->plainTextToken;

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => now()->addDays(1)->timestamp,
        ];
    }

    public function resendSetPasswordLink(array $data): void
    {
        $email = $data['email'];

        $rateLimiterKey = 'resend-set-password-link' . Str::lower($email);;

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);

            throw ValidationException::withMessages([
                'email' => ["Too many requests. Please try again in $seconds seconds."],
            ]);
        }

        $user = User::where('email', $email)->first();

        RateLimiter::hit($rateLimiterKey, decaySeconds: 3600);

        if (!$user || !is_null($user->password)) {
            throw ValidationException::withMessages([
                'email' => ['No user found with this email or password has already been set.'],
            ]);
        }

        event(new UserCreatedByAdmin($user));
    }

    public function setPassword(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $user->forceFill([
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ])->save();
        });
    }

    public function sendResetLink(array $data): void
    {
        $status = Password::sendResetLink([
            'email' => $data['email']
        ]);

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => ['Try again later.'],
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
