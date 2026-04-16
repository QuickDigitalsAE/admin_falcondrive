<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\TestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends BaseApiController
{
    protected string $modelClass = Testimonial::class;
    protected string $resourceClass = TestimonialResource::class;
    protected string $storeRequestClass = TestimonialRequest::class;
    protected string $updateRequestClass = TestimonialRequest::class;
    protected array $searchable = ['name_en', 'name_ar'];
    protected array $with = [];
    protected string $publicMessage = 'Testimonial list fetched successfully';
    protected string $singleMessage = 'Testimonial fetched successfully';
    protected string $storeMessage = 'Testimonial created successfully';
    protected string $updateMessage = 'Testimonial updated successfully';
    protected string $deleteMessage = 'Testimonial deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

}
