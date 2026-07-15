<?php

namespace Modules\Auth\Services;

use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Exceptions\InvalidCredentialsException;
use Modules\Auth\Exceptions\TooManyLoginAttemptsException;
use Modules\Auth\Repositories\UserRepositoryInterface;

class AuthService
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOGIN_DECAY_SECONDS = 60;

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RateLimiter $limiter,
    ) {
    }

    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'free',
        ]);

        event(new UserRegistered($user));

        return $user;
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password, string $throttleKey): array
    {
        if ($this->limiter->tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = $this->limiter->availableIn($throttleKey);

            Log::channel('auth')->warning('Login bloqueado por rate limit', [
                'email' => $email,
                'retry_after' => $seconds,
            ]);

            throw new TooManyLoginAttemptsException($seconds);
        }

        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->limiter->hit($throttleKey, self::LOGIN_DECAY_SECONDS);

            Log::channel('auth')->info('Tentativa de login falhou', ['email' => $email]);

            throw new InvalidCredentialsException;
        }

        $this->limiter->clear($throttleKey);

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::channel('auth')->info('Login realizado com sucesso', [
            'email' => $email,
            'user_id' => $user->id,
        ]);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        Log::channel('auth')->info('Logout realizado', ['user_id' => $user->id]);
    }
}
