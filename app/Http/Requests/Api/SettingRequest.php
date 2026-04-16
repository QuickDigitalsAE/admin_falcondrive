<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class SettingRequest extends BaseDataRequest
{
    public function fillableFields(): array
    {
        return ['key', 'display_name', 'value', 'details', 'type', 'order', 'group'];
    }

    public function rules(?Model $model = null): array
    {
        $id = $model?->getKey();

        return [
            'key' => ['nullable','string','max:191'],
            'display_name' => ['required','string','max:191'],
            'value' => ['nullable','string'],
            'details' => ['nullable','string'],
            'type' => ['required','string','max:191'],
            'order' => ['nullable','integer'],
            'group' => ['nullable','string','max:191'],
            'key' => ['required','string','max:191']
        ];
    }
}
