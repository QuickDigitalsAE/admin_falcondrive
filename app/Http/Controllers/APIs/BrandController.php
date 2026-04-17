<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends BaseApiController
{
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

    public function publicShow(\App\Models\Brand $brand)
    {
        return $this->successResponse($this->singleMessage, $this->transform($brand->load($this->with)));
    }

}
