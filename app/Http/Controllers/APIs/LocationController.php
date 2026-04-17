<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\LocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends BaseApiController
{
    protected string $modelClass = Location::class;
    protected string $resourceClass = LocationResource::class;
    protected string $storeRequestClass = LocationRequest::class;
    protected string $updateRequestClass = LocationRequest::class;
    protected array $searchable = ['name_en', 'name_ar', 'slug'];
    protected array $with = ['cars'];
    protected string $publicMessage = 'Location list fetched successfully';
    protected string $singleMessage = 'Location fetched successfully';
    protected string $storeMessage = 'Location created successfully';
    protected string $updateMessage = 'Location updated successfully';
    protected string $deleteMessage = 'Location deleted successfully';
    protected array $metaDataKeys = [
        'title_en' => ['messages_locations_seo_title_en'],
        'title_ar' => ['messages_locations_seo_title_ar'],
        'description_en' => ['messages_locations_seo_brief_en'],
        'description_ar' => ['messages_locations_seo_brief_ar'],
    ];

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Location $location)
    {
        return $this->successResponse($this->singleMessage, $this->transform($location->load($this->with)));
    }

}
