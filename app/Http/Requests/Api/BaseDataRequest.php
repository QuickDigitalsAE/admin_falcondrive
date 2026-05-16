<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

abstract class BaseDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function sanitize(Request $request, ?Model $model = null): array
    {
        return $request->only($this->fillableFields());
    }

    abstract public function fillableFields(): array;

    public function rules(?Model $model = null): array
    {
        return [];
    }
}
