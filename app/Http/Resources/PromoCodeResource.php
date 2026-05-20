<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'minimum_amount' => (float) $this->minimum_amount,
            'start_date' => optional($this->start_date)->format('Y-m-d'),
            'expiry_date' => optional($this->expiry_date)->format('Y-m-d'),
            'status' => (int) $this->status,
            'status_label' => (int) $this->status === 1 ? 'Active' : 'Inactive',
            'deleted_at' => optional($this->deleted_at)->format('Y-m-d H:i:s'),
            'created_at_human' => optional($this->created_at)->format('d M Y, h:i A'),

            'show_url' => route('admin.promo-codes.show', $this->id),
            'edit_url' => route('admin.promo-codes.edit', $this->id),
            'delete_url' => route('admin.promo-codes.delete', $this->id),
            'restore_url' => route('admin.promo-codes.restore', $this->id),

            'permissions' => [
                'can_view' => auth()->user()?->can('Promo_Code_View') || auth()->user()?->can('Promo_Code_ViewAll') || true,
                'can_edit' => auth()->user()?->can('Promo_Code_Edit') || true,
                'can_delete' => auth()->user()?->can('Promo_Code_Delete') || true,
                'can_restore' => auth()->user()?->can('Promo_Code_Delete') || true,
            ],
        ];
    }
}
