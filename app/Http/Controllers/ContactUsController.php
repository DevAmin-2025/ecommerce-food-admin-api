<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Resources\ContactUsResource;

class ContactUsController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $contactUs = ContactUs::latest()->paginate(5);
        $collection = ContactUsResource::collection($contactUs)->response()->getData();
        return $this->successResponse(
            data: [
                'customer messages' => $collection->data,
                'links' => $collection->links,
                'meta' => $collection->meta,
            ],
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactUs $contactUs): JsonResponse
    {
        return $this->successResponse(
            data: new ContactUsResource($contactUs),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactUs $contactUs): JsonResponse
    {
        $contactUs->delete();
        return $this->successResponse(
            data: null,
            message: "Customer message with id $contactUs->id has been deleted.",
        );
    }
}
