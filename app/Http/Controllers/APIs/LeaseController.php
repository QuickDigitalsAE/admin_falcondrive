<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\LeaseRequest;
use App\Http\Resources\LeaseResource;
use App\Models\Lease;
use Illuminate\Http\Request;

class LeaseController extends BaseApiController
{
    protected string $modelClass = Lease::class;
    protected string $resourceClass = LeaseResource::class;
    protected string $storeRequestClass = LeaseRequest::class;
    protected string $updateRequestClass = LeaseRequest::class;
    protected array $searchable = ['name_en', 'name_ar', 'slug'];
    protected array $with = [];
    protected string $publicMessage = 'Lease list fetched successfully';
    protected string $singleMessage = 'Lease fetched successfully';
    protected string $storeMessage = 'Lease created successfully';
    protected string $updateMessage = 'Lease updated successfully';
    protected string $deleteMessage = 'Lease deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Lease $lease)
    {
        return $this->successResponse($this->singleMessage, $this->transform($lease->load($this->with)));
    }

}
