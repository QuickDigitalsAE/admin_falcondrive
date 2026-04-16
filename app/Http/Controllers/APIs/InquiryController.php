<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\InquiryRequest;
use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends BaseApiController
{
    protected string $modelClass = Inquiry::class;
    protected string $resourceClass = InquiryResource::class;
    protected string $storeRequestClass = InquiryRequest::class;
    protected string $updateRequestClass = InquiryRequest::class;
    protected array $searchable = ['name', 'number', 'email', 'car_name'];
    protected array $with = [];
    protected string $publicMessage = 'Inquiry list fetched successfully';
    protected string $singleMessage = 'Inquiry fetched successfully';
    protected string $storeMessage = 'Inquiry created successfully';
    protected string $updateMessage = 'Inquiry updated successfully';
    protected string $deleteMessage = 'Inquiry deleted successfully';

    public function storePublic(Request $request)
    {
        return $this->store($request);
    }

}
