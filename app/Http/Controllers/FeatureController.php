<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\FeatureResource;
use Illuminate\Support\Facades\Validator;

class FeatureController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $features = Feature::latest()->paginate(5);
        $collection = FeatureResource::collection($features)->response()->getData();
        return $this->successResponse(
            data: [
                'features' => $collection->data,
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
            'title' => 'required|string|unique:features,title',
            'body' => 'required|string|unique:features,title',
            'icon' => 'required|string',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $feature = Feature::create([
            'title' => $request->title,
            'body' => $request->body,
            'icon' => $request->icon,
        ]);
        return $this->successResponse(
            data: new FeatureResource($feature),
            code: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Feature $feature): JsonResponse
    {
        return $this->successResponse(
            data: new FeatureResource($feature),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feature $feature): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'title' => 'nullable|string|unique:features,title,' . $feature->id,
            'body' => 'nullable|string|unique:features,title,' . $feature->id,
            'icon' => 'nullable|string',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $feature->update([
            'title' => $request->filled('title') ? $request->title : $feature->title,
            'body' => $request->filled('body') ? $request->body : $feature->body,
            'icon' => $request->filled('icon') ? $request->icon : $feature->icon,
        ]);
        return $this->successResponse(
            data: new FeatureResource($feature),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feature $feature): JsonResponse
    {
        $feature->delete();
        return $this->successResponse(
            data: null,
            message: "Feature with id $feature->id has been deleted.",
        );
    }
}
