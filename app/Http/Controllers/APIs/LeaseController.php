<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\LeaseRequest;
use App\Http\Resources\CarResource;
use App\Http\Resources\LeaseResource;
use App\Models\Car;
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
    protected array $metaDataKeys = [
        'title_en' => ['lease_meta_title_en', 'messages_lease_title_en'],
        'title_ar' => ['lease_meta_title_ar', 'messages_lease_title_ar'],
        'description_en' => ['lease_meta_description_en', 'messages_lease_brief_en'],
        'description_ar' => ['lease_meta_description_ar', 'messages_lease_brief_ar'],
    ];

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Lease $lease)
    {
        $perPage = max(1, min((int) request()->get('per_page', 15), 100));
        $cars = Car::query()
            ->with(['brand', 'categories', 'locations', 'driverPages'])
            ->orderedForListing()
            ->paginate($perPage)
            ->appends(request()->query());

        return $this->successResponse($this->singleMessage, array_merge(
            $this->transform($lease->load($this->with)),
            [
                'cars' => CarResource::collection($cars)->resolve(),
                'pagination' => [
                    'current_page' => $cars->currentPage(),
                    'last_page' => $cars->lastPage(),
                    'per_page' => $cars->perPage(),
                    'total' => $cars->total(),
                ],
                'brand_list' => $this->allBrandList(),
            ]
        ));
    }

}
