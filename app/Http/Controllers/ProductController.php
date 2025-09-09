<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = Product::latest()->with('category', 'images')->paginate(5);
        $collection = ProductResource::collection($products)->response()->getData();
        return $this->successResponse(
            data: [
                'products' => $collection->data,
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
            'name' => 'required|string|unique:products,name',
            'category_id' => 'required|exists:categories,id',
            'primary_image' => 'required|image|max:1024',
            'images' => 'nullable',
            'images.*' => 'image|max:1024',
            'description' => 'required|string',
            'price' => 'required',
            'quantity' => 'required',
            'status' => 'nullable|boolean',
            'sale_price' => 'nullable',
            'date_on_sale_from' => 'nullable|date_format:Y/m/d H:i:s',
            'date_on_sale_to' => 'nullable|date_format:Y/m/d H:i:s',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $primaryImg = Carbon::now()->micro . '-' . $request->primary_image->getClientOriginalName();
        $request->primary_image->storeAs('images/products', $primaryImg);
        if ($request->hasFile('images')) {
            $productImages = [];
            foreach ($request->images as $image) {
                $productImg = Carbon::now()->micro . '-' . $image->getClientOriginalName();
                array_push($productImages, $productImg);
                $image->storeAs('images/products', $productImg);
            };
        };

        try {
            DB::beginTransaction();
            $product = Product::create([
                'name' => $request->name,
                'slug' => $request->string('name')->slug(),
                'category_id' => $request->category_id,
                'primary_image' => $primaryImg,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'status' => $request->filled('status') ? $request->status : 1,
                'sale_price' => $request->filled('sale_price') ? $request->sale_price : null,
                'date_on_sale_from' => $request->filled('date_on_sale_from') ? $request->date_on_sale_from : null,
                'date_on_sale_to' => $request->filled('date_on_sale_to') ? $request->date_on_sale_to : null,
            ]);
            if ($request->hasFile('images')) {
                foreach ($productImages as $productImage) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $productImage,
                    ]);
                };
            };
            DB::commit();
            return $this->successResponse(
                data: new ProductResource($product->load('category', 'images')),
                code : 201,
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(
                message: $e->getMessage(),
                code: 500,
            );
        };
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(
            data: new ProductResource($product->load('category', 'images')),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'nullable|string|unique:products,name,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'primary_image' => 'nullable|image|max:1024',
            'images' => 'nullable',
            'images.*' => 'image|max:1024',
            'description' => 'nullable|string',
            'price' => 'nullable',
            'quantity' => 'nullable',
            'status' => 'nullable|boolean',
            'sale_price' => 'nullable',
            'date_on_sale_from' => 'nullable|date_format:Y/m/d H:i:s',
            'date_on_sale_to' => 'nullable|date_format:Y/m/d H:i:s',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        if ($request->hasFile('primary_image')) {
            Storage::delete('images/products/' . $product->primary_image);
            $primaryImg = Carbon::now()->micro . '-' . $request->primary_image->getClientOriginalName();
            $request->primary_image->storeAs('images/products', $primaryImg);
        };
        if ($request->hasFile('images')) {
            foreach ($product->images as $productImage) {
                Storage::delete('images/products/' . $productImage->image);
            };
            $productImages = [];
            foreach ($request->images as $image) {
                $productImg = Carbon::now()->micro . '-' . $image->getClientOriginalName();
                array_push($productImages, $productImg);
                $image->storeAs('images/products', $productImg);
            };
        };

        try {
            DB::beginTransaction();
            $product->update([
                'name' => $request->filled('name') ? $request->name : $product->name,
                'slug' => $request->filled('name') ? $request->string('name')->slug() : $product->slug,
                'category_id' => $request->filled('category_id') ? $request->category_id : $product->category_id,
                'primary_image' => $request->hasFile('primary_image') ? $primaryImg : $product->primary_image,
                'description' => $request->filled('description') ? $request->description : $product->description,
                'price' => $request->filled('price') ? $request->price : $product->price,
                'quantity' => $request->filled('quantity') ? $request->quantity : $product->quantity,
                'status' => $request->filled('status') ? $request->status : $product->status,
                'sale_price' => $request->filled('sale_price') ? $request->sale_price : $product->sale_price,
                'date_on_sale_from' => $request->filled('date_on_sale_from') ?
                    $request->date_on_sale_from : $product->date_on_sale_from,
                'date_on_sale_to' => $request->filled('date_on_sale_to') ?
                    $request->date_on_sale_to : $product->date_on_sale_to,
            ]);
            if ($request->hasFile('images')) {
                ProductImage::where('product_id', $product->id)->delete();
                foreach ($productImages as $productImage) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $productImage,
                    ]);
                };
            };
            DB::commit();
            return $this->successResponse(
                data: new ProductResource($product->load('category', 'images')),
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse(
                message: $e->getMessage(),
                code: 500,
            );
        };
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        Storage::delete('images/products/' . $product->primary_image);
        foreach ($product->images as $productImage) {
            Storage::delete('images/products/' . $productImage->image);
            $productImage->delete();
        };
        $product->delete();

        return $this->successResponse(
            data: null,
            message: "Product with id $product->id has been deleted.",
        );
    }
}
