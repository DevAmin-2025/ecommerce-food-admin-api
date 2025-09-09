<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\AboutUsResource;
use Illuminate\Support\Facades\Validator;

class AboutUsController extends ApiController
{
    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $aboutUs = AboutUs::firstOrFail();
        return $this->successResponse(
            data: new AboutUsResource($aboutUs),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'body' => 'nullable|string',
            'link_title' => 'nullable|string',
            'link_address' => 'nullable|string',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $aboutUs = AboutUs::firstOrFail();
        $aboutUs->update([
            'title' => $request->filled('title') ? $request->title : $aboutUs->title,
            'body' => $request->filled('body') ? $request->body : $aboutUs->body,
            'link_title' => $request->filled('link_title') ? $request->link_title : $aboutUs->link_title,
            'link_address' => $request->filled('link_address') ? $request->link_address : $aboutUs->link_address,
        ]);
        return $this->successResponse(
            data: new AboutUsResource($aboutUs)
        );
    }
}
