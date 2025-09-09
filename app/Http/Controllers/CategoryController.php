<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Validator;

class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::latest()->with('products')->paginate(5);
        $collection = CategoryResource::collection($categories)->response()->getData();
        return $this->successResponse(
            data: [
                'categories' => $collection->data,
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
            'name' => 'required|string|unique:categories,name',
            'status' => 'nullable|boolean',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $category = Category::create($validate->validated());
        return $this->successResponse(
            data: new CategoryResource($category),
            code: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return $this->successResponse(
            data: new CategoryResource($category->load('products')),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'nullable|string|unique:categories,name,' . $category->id,
            'status' => 'nullable|boolean',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $category->update([
            'name' => $request->filled('name') ? $request->name : $category->name,
            'status' => $request->filled('status') ? $request->status : $category->status,
        ]);
        return $this->successResponse(
            data: new CategoryResource($category),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return $this->successResponse(
            data: null,
            message: "Category with id $category->id has been deleted.",
        );
    }
}
