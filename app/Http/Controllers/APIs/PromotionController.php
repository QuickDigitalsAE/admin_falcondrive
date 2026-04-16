<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\PromotionRequest;
use App\Http\Resources\PromotionResource;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends BaseApiController
{
    protected string $modelClass = Promotion::class;
    protected string $resourceClass = PromotionResource::class;
    protected string $storeRequestClass = PromotionRequest::class;
    protected string $updateRequestClass = PromotionRequest::class;
    protected array $searchable = ['name_en', 'name_ar', 'slug'];
    protected array $with = [];
    protected string $publicMessage = 'Promotion list fetched successfully';
    protected string $singleMessage = 'Promotion fetched successfully';
    protected string $storeMessage = 'Promotion created successfully';
    protected string $updateMessage = 'Promotion updated successfully';
    protected string $deleteMessage = 'Promotion deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Promotion $promotion)
    {
        return $this->successResponse($this->singleMessage, $this->transform($promotion->load($this->with)));
    }

}
