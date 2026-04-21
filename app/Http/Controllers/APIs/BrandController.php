<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\APIs\Concerns\InteractsWithCarListings;
use App\Http\Requests\Api\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends BaseApiController
{
    use InteractsWithCarListings;

    protected string $modelClass = Brand::class;
    protected string $resourceClass = BrandResource::class;
    protected string $storeRequestClass = BrandRequest::class;
    protected string $updateRequestClass = BrandRequest::class;
    protected array $searchable = ['name_en', 'name_ar', 'slug'];
    protected array $with = ['cars'];
    protected string $publicMessage = 'Brand list fetched successfully';
    protected string $singleMessage = 'Brand fetched successfully';
    protected string $storeMessage = 'Brand created successfully';
    protected string $updateMessage = 'Brand updated successfully';
    protected string $deleteMessage = 'Brand deleted successfully';
    protected array $sortable = ['id', 'name_en', 'sorting'];
    protected array $metaDataKeys = [
        'title_en' => ['brands_meta_title_en', 'brand_meta_title_en', 'messages_brands_title_en'],
        'title_ar' => ['brands_meta_title_ar', 'brand_meta_title_ar', 'messages_brands_title_ar'],
        'description_en' => ['brands_meta_description_en', 'brand_meta_description_en', 'messages_brands_brief_en'],
        'description_ar' => ['brands_meta_description_ar', 'brand_meta_description_ar', 'messages_brands_brief_ar'],
    ];

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    protected function query(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::query($request);

        if (! $request->filled('sort_by')) {
            $query->reorder()->orderedForListing();
        }

        return $query;
    }

    public function publicShow(Request $request, \App\Models\Brand $brand)
    {
        $payload = $this->paginatedCarListingPayload(
            $request,
            fn ($query) => $query->where('brand_id', $brand->id)
        );

        unset($payload['brand_list']);

        return $this->successResponse($this->singleMessage, array_merge(
            $this->transform($brand),
            $payload
        ));
    }

}
