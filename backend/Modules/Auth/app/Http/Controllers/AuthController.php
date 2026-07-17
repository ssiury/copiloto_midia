<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\AuthApplication;
use Modules\Auth\Application\Data\LoginData;
use Modules\Auth\Application\Data\RegisterData;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Resources\AuthResource;
use Modules\Auth\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthApplication $authApplication,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authApplication->register(new RegisterData(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        ));

        return (new UserResource($user))
            ->additional(['meta' => (object) []])
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authApplication->login(new LoginData(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            throttleKey: $request->throttleKey(),
        ));

        return (new AuthResource($result))
            ->additional(['meta' => (object) []])
            ->response();
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authApplication->logout($request->user());

        return response()->json([
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->additional(['meta' => (object) []])
            ->response();
    }
}
