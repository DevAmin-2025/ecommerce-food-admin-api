<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'percent' => $this->percent,
            'expires_at' => verta($this->expires_at)->formatDatetime(),
            'expires_at_gregorian' => $this->expires_at,
        ];
    }
}
