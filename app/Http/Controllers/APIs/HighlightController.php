<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\HighlightRequest;
use App\Http\Resources\HighlightResource;
use App\Models\Highlight;
use Illuminate\Http\Request;

class HighlightController extends BaseApiController
{
    protected string $modelClass = Highlight::class;
    protected string $resourceClass = HighlightResource::class;
    protected string $storeRequestClass = HighlightRequest::class;
    protected string $updateRequestClass = HighlightRequest::class;
    protected array $searchable = ['title_en', 'title_ar', 'url'];
    protected array $with = [];
    protected string $publicMessage = 'Highlight list fetched successfully';
    protected string $singleMessage = 'Highlight fetched successfully';
    protected string $storeMessage = 'Highlight created successfully';
    protected string $updateMessage = 'Highlight updated successfully';
    protected string $deleteMessage = 'Highlight deleted successfully';
    protected array $sortable = ['id', 'title_en', 'title_ar', 'sorting', 'url'];

    protected function query(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::query($request);

        if (!$request->filled('sort_by')) {
            $query->reorder()->orderedForListing();
        }

        return $query;
    }

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

}
