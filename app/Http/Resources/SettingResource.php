<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
class SettingResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'display_name' => $this->display_name,
            'value' => $this->value,
            'details' => $this->details,
            'type' => $this->type,
            'order' => $this->order,
            'group' => $this->group,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
