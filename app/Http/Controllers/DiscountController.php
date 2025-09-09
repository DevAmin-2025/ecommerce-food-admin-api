<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\DiscountResource;
use Illuminate\Support\Facades\Validator;

class DiscountController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $discounts = Discount::latest()->paginate(5);
        $collection = DiscountResource::collection($discounts)
        ->response()
        ->getData();
        return $this->successResponse(
            data: [
                'coupons' => $collection->data,
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
            'code' => 'required|string|unique:discounts,code',
            'percent' => 'required|integer',
            'expires_at' => 'required|date_format:Y/m/d H:i:s',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $discount = Discount::create([
            'code' => $request->code,
            'percent' => $request->percent,
            'expires_at' => $request->expires_at,
        ]);
        return $this->successResponse(
            data: new DiscountResource($discount),
            code: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $coupon): JsonResponse
    {
        return $this->successResponse(
            data: new DiscountResource($coupon),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $coupon): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'code' => 'nullable|string|unique:discounts,code,' . $coupon->id,
            'percent' => 'nullable|integer',
            'expires_at' => 'nullable|date_format:Y/m/d H:i:s',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $coupon->update([
            'code' => $request->filled('code') ? $request->code : $coupon->code,
            'percent' => $request->filled('percent') ? $request->percent : $coupon->percent,
            'expires_at' => $request->filled('expires_at') ? $request->expires_at : $coupon->expires_at,
        ]);
        return $this->successResponse(
            data: new DiscountResource($coupon),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $coupon): JsonResponse
    {
        $coupon->delete();
        return $this->successResponse(
            data: null,
            message: "Discount with id $coupon->id has been deleted.",
        );
    }
}
