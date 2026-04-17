<?php

namespace App\Http\Controllers\APIs;

use App\Http\Requests\Api\CarWithDriverRequest;
use App\Http\Resources\CarWithDriverResource;
use App\Models\CarWithDriver;
use Illuminate\Http\Request;

class CarWithDriverController extends BaseApiController
{
    protected string $modelClass = CarWithDriver::class;
    protected string $resourceClass = CarWithDriverResource::class;
    protected string $storeRequestClass = CarWithDriverRequest::class;
    protected string $updateRequestClass = CarWithDriverRequest::class;
    protected array $searchable = ['display_en', 'display_ar'];
    protected array $with = ['carsRelation'];
    protected string $publicMessage = 'CarWithDriver list fetched successfully';
    protected string $singleMessage = 'CarWithDriver fetched successfully';
    protected string $storeMessage = 'CarWithDriver created successfully';
    protected string $updateMessage = 'CarWithDriver updated successfully';
    protected string $deleteMessage = 'CarWithDriver deleted successfully';
    protected array $metaDataKeys = [
        'title_en' => ['messages_cars_with_driver_title_en'],
        'title_ar' => ['messages_cars_with_driver_title_ar'],
        'description_en' => ['messages_cars_with_driver_brief_en'],
        'description_ar' => ['messages_cars_with_driver_brief_ar'],
    ];

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function publicShow(\App\Models\CarWithDriver $carWithDriver)
    {
        return $this->successResponse($this->singleMessage, $this->transform($carWithDriver->load($this->with)));
    }


}
