<?php

namespace App\Http\Controllers;

use App\Models\Footer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FooterResource;
use App\Http\Controllers\ApiController;

class FooterController extends ApiController
{
    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $footer = Footer::firstOrFail();
        return $this->successResponse(
            data: new FooterResource($footer),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'contact_address' => 'nullable|string',
            'contact_phone' => 'nullable|string|regex:/^09[0-3][0-9]{8}$/',
            'contact_email' => 'nullable|email',
            'title' => 'nullable|string',
            'body' => 'nullable|string',
            'work_days' => 'nullable|string',
            'work_hour_from' => 'nullable|string',
            'work_hour_to' => 'nullable|string',
            'telegram_link' => 'nullable|url',
            'whatsapp_link' => 'nullable|url',
            'instagram_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'copyright' => 'nullable|string',
        ]);
        if ($validate->fails()) {
            return $this->errorResponse(
                message: $validate->errors(),
                code: 422,
            );
        };

        $footer = Footer::firstOrFail();
        $footer->update([
            'contact_address' => $request->filled('contact_address') ? $request->contact_address : $footer->contact_address,
            'contact_phone' => $request->filled('contact_phone') ? $request->contact_phone : $footer->contact_phone,
            'contact_email' => $request->filled('contact_email') ? $request->contact_email : $footer->contact_email,
            'title' => $request->filled('title') ? $request->title : $footer->title,
            'body' => $request->filled('body') ? $request->body : $footer->body,
            'work_days' => $request->filled('work_days') ? $request->work_days : $footer->work_days,
            'work_hour_from' => $request->filled('work_hour_from') ? $request->work_hour_from : $footer->work_hour_from,
            'work_hour_to' => $request->filled('work_hour_to') ? $request->work_hour_to : $footer->work_hour_to,
            'telegram_link' => $request->filled('telegram_link') ? $request->telegram_link : $footer->telegram_link,
            'whatsapp_link' => $request->filled('whatsapp_link') ? $request->whatsapp_link : $footer->whatsapp_link,
            'instagram_link' => $request->filled('instagram_link') ? $request->instagram_link : $footer->instagram_link,
            'youtube_link' => $request->filled('youtube_link') ? $request->youtube_link : $footer->youtube_link,
            'copyright' => $request->filled('copyright') ? $request->copyright : $footer->copyright,
        ]);
        return $this->successResponse(
            data: new FooterResource($footer)
        );
    }
}
