<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\BlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends BaseApiController
{
    protected string $modelClass = Blog::class;
    protected string $resourceClass = BlogResource::class;
    protected string $storeRequestClass = BlogRequest::class;
    protected string $updateRequestClass = BlogRequest::class;
    protected array $searchable = ['title_en', 'title_ar', 'slug'];
    protected array $with = [];
    protected string $publicMessage = 'Blog list fetched successfully';
    protected string $singleMessage = 'Blog fetched successfully';
    protected string $storeMessage = 'Blog created successfully';
    protected string $updateMessage = 'Blog updated successfully';
    protected string $deleteMessage = 'Blog deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Blog $blog)
    {
        return $this->successResponse($this->singleMessage, $this->transform($blog->load($this->with)));
    }

}
