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
    protected array $metaDataKeys = [
        'title_en' => ['categories_meta_title_en', 'category_meta_title_en'],
        'title_ar' => ['categories_meta_title_ar', 'category_meta_title_ar'],
        'description_en' => ['categories_meta_description_en', 'category_meta_description_en'],
        'description_ar' => ['categories_meta_description_ar', 'category_meta_description_ar'],
    ];

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\Category $category)
    {
        $category->load(['cars.brand']);

        return $this->successResponse($this->singleMessage, array_merge(
            $this->transform($category),
            [
                'brand_list' => $this->brandListFromCars($category->cars),
            ]
        ));
    }
}
