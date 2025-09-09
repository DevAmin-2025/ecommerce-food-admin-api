<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::latest()->paginate(5);
        $collection = UserResource::collection($users)->response()->getData();
        return $this->successResponse(
            data: [
                'users' => $collection->data,
                'links' => $collection->links,
                'meta' => $collection->meta,
            ],
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'roles' => 'nullable',
            'roles.*' => 'string|exists:roles,name',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $user = User::create($validate->validated());
        if ($request->filled('roles')) {
            $roleIds = Role::whereIn('name', $request->roles)->pluck('id');
            $user->roles()->attach($roleIds);
        };
        return $this->successResponse(
            data: new UserResource($user->load('roles')),
            code: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return $this->successResponse(
            data: new UserResource($user),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'roles' => 'nullable',
            'roles.*' => 'string|exists:roles,name',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $password = $request->filled('password') ? Hash::make($request->password) : $user->password;
        $user->update([
            'name' => $request->filled('name') ? $request->name : $user->name,
            'email' => $request->filled('email') ? $request->email : $user->email,
            'password' => $password,
        ]);
        if ($request->filled('roles')) {
            $roleIds = Role::whereIn('name', $request->roles)->pluck('id');
            $user->roles()->sync($roleIds);
        };
        return $this->successResponse(
            data: new UserResource($user->load('roles')),
        );
    }
}
