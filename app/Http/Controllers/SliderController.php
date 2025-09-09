<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SliderResource;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SliderController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $sliders = Slider::latest()->paginate(5);
        $collection = SliderResource::collection($sliders)->response()->getData();
        return $this->successResponse(
            data: [
                'sliders' => $collection->data,
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
            'title' => 'required|string|unique:sliders,title',
            'body' => 'required|string|unique:sliders,title',
            'image' => 'required|image|max:1024',
            'link_title' => 'required|string',
            'link_address' => 'required|string',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $fileName = Carbon::now()->micro . '-' . $request->image->getClientOriginalName();
        $request->image->storeAs('images/sliders', $fileName);
        $slider = Slider::create([
            'title' => $request->title,
            'body' => $request->body,
            'image' => $fileName,
            'link_title' => $request->link_title,
            'link_address' => $request->link_address,
        ]);
        return $this->successResponse(
            data: new SliderResource($slider),
            code: 201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Slider $slider): JsonResponse
    {
        return $this->successResponse(
            data: new SliderResource($slider),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Slider $slider): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'title' => 'nullable|string|unique:sliders,title,' . $slider->id,
            'body' => 'nullable|string|unique:sliders,title,' . $slider->id,
            'image' => 'nullable|image|max:1024',
            'link_title' => 'nullable|string',
            'link_address' => 'nullable|string',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };
        if ($request->hasFile('image')) {
            Storage::delete('images/sliders/' . $slider->image);
            $fileName = Carbon::now()->micro . '-' . $request->image->getClientOriginalName();
            $request->image->storeAs('images/sliders', $fileName);
        };

        $slider->update([
            'title' => $request->filled('title') ? $request->title : $slider->title,
            'body' => $request->filled('body') ? $request->body : $slider->body,
            'image' => $request->hasFile('image') ? $fileName : $slider->image,
            'link_title' => $request->filled('link_title') ? $request->link_title : $slider->link_title,
            'link_address' => $request->filled('link_address') ? $request->link_address : $slider->link_address,
        ]);
        return $this->successResponse(
            data: new SliderResource($slider),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slider $slider): JsonResponse
    {
        Storage::delete('images/sliders/' . $slider->image);
        $slider->delete();
        return $this->successResponse(
            data: null,
            message: "Slider with id $slider->id has been deleted.",
        );
    }
}
