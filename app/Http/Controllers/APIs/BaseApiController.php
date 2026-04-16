<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

            return $this->successResponse(
                $this->publicMessage,
                $this->paginatedData($records, $resource::collection($records)->resolve())
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
            $request->validate(app($this->storeRequestClass)->rules());
            $data = app($this->storeRequestClass)->sanitize($request);
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
            $request->validate(app($this->updateRequestClass)->rules($record));
            $data = app($this->updateRequestClass)->sanitize($request, $record);
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
}
