<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderResource;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class OrderController extends ApiController
{
    public function index(): JsonResponse
    {
        $orders = Order::latest()->with('userAddress', 'orderItems.product')->paginate(5);
        $collection = OrderResource::collection($orders)->response()->getData();
        return $this->successResponse(
            data: [
                'orders' => $collection->data,
                'links' => $collection->links,
                'meta' => $collection->meta,
            ],
        );
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'status' => 'required|integer',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $order->update([
            'status' => $request->status,
        ]);
        return $this->successResponse(
            data: new OrderResource($order->load('userAddress')),
        );
    }
}
