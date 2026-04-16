<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\SettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseApiController
{
    protected string $modelClass = Setting::class;
    protected string $resourceClass = SettingResource::class;
    protected string $storeRequestClass = SettingRequest::class;
    protected string $updateRequestClass = SettingRequest::class;
    protected array $searchable = ['key', 'display_name', 'group'];
    protected array $with = [];
    protected string $publicMessage = 'Setting list fetched successfully';
    protected string $singleMessage = 'Setting fetched successfully';
    protected string $storeMessage = 'Setting created successfully';
    protected string $updateMessage = 'Setting updated successfully';
    protected string $deleteMessage = 'Setting deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

}
