<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Setting;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;

    protected string $modelClass;
    protected string $resourceClass;
    protected string $storeRequestClass;
    protected string $updateRequestClass;
    protected array $searchable = [];
    protected array $sortable = ['id'];
    protected array $with = [];
    protected string $defaultSort = 'id';
    protected string $defaultDirection = 'desc';
    protected string $publicMessage = 'List fetched successfully';
    protected string $singleMessage = 'Record fetched successfully';
    protected string $storeMessage = 'Record created successfully';
    protected string $updateMessage = 'Record updated successfully';
    protected string $deleteMessage = 'Record deleted successfully';
    protected array $metaDataKeys = [];

    protected function model(): Model
    {
        return new $this->modelClass();
    }

    protected function query(Request $request): Builder
    {
        $query = $this->model()->newQuery()->with($this->with);

        if ($request->filled('search') && count($this->searchable) > 0) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $inner) use ($search) {
                foreach ($this->searchable as $index => $field) {
                    if ($index === 0) {
                        $inner->where($field, 'like', "%{$search}%");
                    } else {
                        $inner->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        return $this->applyFilters($query, $request);
    }

    protected function applyFilters(Builder $query, Request $request): Builder
    {
        $sortBy = in_array($request->get('sort_by'), $this->sortable, true)
            ? $request->get('sort_by')
            : $this->defaultSort;

        $direction = strtolower((string) $request->get('sort_direction', $this->defaultDirection));
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : $this->defaultDirection;

        return $query->orderBy($sortBy, $direction);
    }

    protected function resolveRecord(int|string $id): Model
    {
        return $this->modelClass::with($this->with)->findOrFail($id);
    }

    protected function transform(Model $record): array
    {
        $resource = $this->resourceClass;
        return $resource::make($record)->resolve();
    }

    public function index(Request $request)
    {
        try {
            $perPage = max(1, min((int) $request->get('per_page', 15), 100));
            $records = $this->query($request)->paginate($perPage)->appends($request->query());
            $resource = $this->resourceClass;
            $data = $this->paginatedData($records, $resource::collection($records)->resolve());
            $metaData = $this->buildMetaData($request);

            if (!empty(array_filter($metaData, fn ($value) => $value !== null && $value !== ''))) {
                $data['meta_data'] = $metaData;
            }

            return $this->successResponse(
                $this->publicMessage,
                $data
            );
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    public function show(int|string $id)
    {
        try {
            return $this->successResponse($this->singleMessage, $this->transform($this->resolveRecord($id)));
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $formRequest = new $this->storeRequestClass();
            $request->validate($formRequest->rules());
            $data = $formRequest->sanitize($request);
            $record = $this->modelClass::create($data);
            $record->load($this->with);

            return $this->successResponse($this->storeMessage, $this->transform($record), 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', ['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    public function update(Request $request, int|string $id)
    {
        try {
            $record = $this->resolveRecord($id);
            $formRequest = new $this->updateRequestClass();
            $request->validate($formRequest->rules($record));
            $data = $formRequest->sanitize($request, $record);
            $record->update($data);
            $record->load($this->with);

            return $this->successResponse($this->updateMessage, $this->transform($record));
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', ['errors' => $e->errors()], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    public function destroy(int|string $id)
    {
        try {
            $record = $this->resolveRecord($id);
            $record->delete();

            return $this->successResponse($this->deleteMessage, ['id' => $record->getKey()]);
        } catch (Throwable $e) {
            return $this->errorResponse($e->getMessage(), ['exception' => class_basename($e)], 500);
        }
    }

    protected function buildMetaData(Request $request): array
    {
        if (empty($this->metaDataKeys)) {
            return $this->fallbackMetaData($request);
        }

        $settings = $this->siteSettings();
        $fallback = $this->fallbackMetaData($request);
        $metaData = [];

        foreach ($this->metaDataKeys as $field => $keys) {
            $metaData[$field] = $this->settingValue($settings, (array) $keys, $fallback[$field] ?? null);
        }

        return $metaData;
    }

    protected function fallbackMetaData(Request $request): array
    {
        return [];
    }

    protected function siteSettings(): Collection
    {
        return Setting::query()
            ->where('group', 'site')
            ->get(['key', 'value'])
            ->keyBy('key');
    }

    protected function settingValue(Collection $settings, array $keys, ?string $default = null): ?string
    {
        foreach ($keys as $key) {
            $value = $settings->get($key)?->value;
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return $default;
    }

    protected function allBrandList(): array
    {
        return Brand::query()
            ->select(['id', 'name_en', 'name_ar', 'slug', 'logo', 'sorting'])
            ->orderedForListing()
            ->get()
            ->map(fn (Brand $brand) => $this->transformBrandListItem($brand))
            ->values()
            ->all();
    }

    protected function allCategoryList(): array
    {
        return Category::query()
            ->select(['id', 'name_en', 'name_ar', 'slug', 'image'])
            ->orderByRaw('LOWER(COALESCE(name_en, "")) ASC')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Category $category) => $this->transformCategoryListItem($category))
            ->values()
            ->all();
    }

    protected function transformBrandListItem(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'name_en' => $brand->name_en,
            'name_ar' => $brand->name_ar,
            'slug' => $brand->slug,
            'logo_url' => BrandResource::make($brand)->resolve()['logo_url'] ?? null,
        ];
    }

    protected function transformCategoryListItem(Category $category): array
    {
        return [
            'id' => $category->id,
            'name_en' => $category->name_en,
            'name_ar' => $category->name_ar,
            'slug' => $category->slug,
            'image_url' => CategoryResource::make($category)->resolve()['image_url'] ?? null,
        ];
    }

    protected function brandListFromCars(Collection $cars): array
    {
        $brandIds = $cars
            ->pluck('brand.id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($brandIds)) {
            return [];
        }

        return Brand::query()
            ->whereIn('id', $brandIds)
            ->orderedForListing()
            ->get()
            ->map(fn (Brand $brand) => $this->transformBrandListItem($brand))
            ->all();
    }
}
