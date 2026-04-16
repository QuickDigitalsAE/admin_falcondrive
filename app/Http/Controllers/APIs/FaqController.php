<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\FaqRequest;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends BaseApiController
{
    protected string $modelClass = Faq::class;
    protected string $resourceClass = FaqResource::class;
    protected string $storeRequestClass = FaqRequest::class;
    protected string $updateRequestClass = FaqRequest::class;
    protected array $searchable = ['question_en', 'question_ar'];
    protected array $with = [];
    protected string $publicMessage = 'Faq list fetched successfully';
    protected string $singleMessage = 'Faq fetched successfully';
    protected string $storeMessage = 'Faq created successfully';
    protected string $updateMessage = 'Faq updated successfully';
    protected string $deleteMessage = 'Faq deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

}
