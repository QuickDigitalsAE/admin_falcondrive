<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\AboutUsRequest;
use App\Http\Resources\AboutUsResource;
use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends BaseApiController
{
    protected string $modelClass = AboutUs::class;
    protected string $resourceClass = AboutUsResource::class;
    protected string $storeRequestClass = AboutUsRequest::class;
    protected string $updateRequestClass = AboutUsRequest::class;
    protected array $searchable = ['id'];
    protected array $with = [];
    protected string $publicMessage = 'AboutUs list fetched successfully';
    protected string $singleMessage = 'AboutUs fetched successfully';
    protected string $storeMessage = 'AboutUs created successfully';
    protected string $updateMessage = 'AboutUs updated successfully';
    protected string $deleteMessage = 'AboutUs deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

}
