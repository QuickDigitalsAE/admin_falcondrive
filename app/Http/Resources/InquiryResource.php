<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class InquiryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'number' => $this->number,
            'email' => $this->email,
            'message' => $this->message,
            'promo_code' => $this->promo_code,
            'car_name' => $this->car_name,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
