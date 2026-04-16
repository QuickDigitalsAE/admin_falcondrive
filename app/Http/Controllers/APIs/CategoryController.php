<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    protected string $modelClass = Category::class;
    protected string $resourceClass = CategoryResource::class;
    protected string $storeRequestClass = CategoryRequest::class;
    protected string $updateRequestClass = CategoryRequest::class;
    protected array $searchable = ['name_en', 'name_ar', 'slug'];
    protected array $with = ['cars'];
    protected string $publicMessage = 'Category list fetched successfully';
    protected string $singleMessage = 'Category fetched successfully';
    protected string $storeMessage = 'Category created successfully';
    protected string $updateMessage = 'Category updated successfully';
    protected string $deleteMessage = 'Category deleted successfully';

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Category $category)
    {
        return $this->successResponse($this->singleMessage, $this->transform($category->load($this->with)));
    }

}
