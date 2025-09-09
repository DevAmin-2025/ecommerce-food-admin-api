<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RoleResource;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class RoleController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::latest()->get();
        return $this->successResponse(
            data: RoleResource::collection($roles),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $role = Role::create($validate->validated());
        return $this->successResponse(
            data: new RoleResource($role),
            code: 201,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $role->update($validate->validated());
        return $this->successResponse(
            data: new RoleResource($role),
        );
    }
}
