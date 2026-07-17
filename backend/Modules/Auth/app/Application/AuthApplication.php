<?php

namespace Modules\Auth\Application;

use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Application\Data\LoginData;
use Modules\Auth\Application\Data\LoginResult;
use Modules\Auth\Application\Data\RegisterData;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Exceptions\InvalidCredentialsException;
use Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Modules\Auth\Repositories\UserRepositoryInterface;

class AuthApplication
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOGIN_DECAY_SECONDS = 60;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RateLimiter $limiter,
    ) {
    }

    public function register(RegisterData $data): User
    {
        $user = $this->users->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'user_type' => 'free',
        ]);

        event(new UserRegistered($user));

        return $user;
    }

    public function login(LoginData $data): LoginResult
    {
        if ($this->limiter->tooManyAttempts($data->throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = $this->limiter->availableIn($data->throttleKey);

            Log::channel('auth')->warning('Login bloqueado por rate limit', [
                'email' => $data->email,
                'retry_after' => $seconds,
            ]);

            throw new TooManyLoginAttemptsException($seconds);
        }

        $user = $this->users->findByEmail($data->email);

        if (! $user || ! Hash::check($data->password, $user->password)) {
            $this->limiter->hit($data->throttleKey, self::LOGIN_DECAY_SECONDS);

            Log::channel('auth')->info('Tentativa de login falhou', ['email' => $data->email]);

            throw new InvalidCredentialsException;
        }

        $this->limiter->clear($data->throttleKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::channel('auth')->info('Login realizado com sucesso', [
            'email' => $data->email,
            'user_id' => $user->id,
        ]);

        return new LoginResult(user: $user, token: $token);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        Log::channel('auth')->info('Logout realizado', ['user_id' => $user->id]);
    }
}
