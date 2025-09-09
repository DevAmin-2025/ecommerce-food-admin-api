<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $user = User::where('email', $request->email)->firstOrFail();
        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                message: 'Invalid password',
                code: 401,
            );
        };

        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->successResponse(
            data: [
                'token' => $token,
                'type' => 'Bearer',
                'user' => new UserResource($user),
            ],
            message: 'You have successfully logged in.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return $this->successResponse(
            data: null,
            message: 'You have successfully logged out.',
        );
    }
}
