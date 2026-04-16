<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarWithDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CarWithDriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:CarWithDriver_ViewAll|CarWithDriver_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:CarWithDriver_ViewAll|CarWithDriver_ViewMine|CarWithDriver_View', ['only' => ['show']]);
        $this->middleware('permission:CarWithDriver_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:CarWithDriver_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:CarWithDriver_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:CarWithDriver_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('CarWithDriver_ViewAll')) {
            return $this->listRecords($request);
        }

        if ($user->can('CarWithDriver_ViewMine')) {
            return $this->listRecords($request, true);
        }

        abort(403, 'You do not have permission to view car with driver records.');
    }

    public function create()
    {
        return view('admin.car-with-drivers.create', [
            'cars' => Car::orderBy('name_en')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $carIds = $this->normalizeCarIds($validated['car_ids'] ?? []);

        $record = CarWithDriver::create($this->buildPayload($validated, $request) + [
            'slug' => $this->generateUniqueSlug($validated['slug'] ?? $validated['display_en']),
            'cars' => $this->encodeCarIds($carIds),
            'created_by' => Auth::id(),
        ]);

        $record->carsRelation()->sync($this->buildCarPivotPayload($carIds));

        return redirect()->route('admin.car-with-drivers')->with('success', 'Car with driver added successfully.');
    }

    public function show(int $id)
    {
        $record = CarWithDriver::withTrashed()
            ->with(['carsRelation', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            ->find($id);

        if (!$record) {
            return redirect()->route('admin.car-with-drivers')->with('error', 'Car with driver record not found.');
        }

        return view('admin.car-with-drivers.show', compact('record'));
    }

    public function edit(int $id)
    {
        $record = CarWithDriver::with('carsRelation')->find($id);

        if (!$record) {
            return redirect()->route('admin.car-with-drivers')->with('error', 'Car with driver record not found.');
        }

        return view('admin.car-with-drivers.edit', [
            'record' => $record,
            'cars' => Car::orderBy('name_en')->get(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $record = CarWithDriver::with('carsRelation')->find($id);

        if (!$record) {
            return redirect()->route('admin.car-with-drivers')->with('error', 'Car with driver record not found.');
        }

        $validated = $request->validate($this->validationRules($record->id));
        $carIds = $this->normalizeCarIds($validated['car_ids'] ?? []);

        if ($request->hasFile('card_image')) {
            $this->deleteImage($record->card_image);
        }

        $record->fill($this->buildPayload($validated, $request, $record));
        $record->slug = $this->generateUniqueSlug($validated['slug'] ?? $validated['display_en'], $record->id);
        $record->cars = $this->encodeCarIds($carIds);
        $record->updated_by = Auth::id();
        $record->save();

        $record->carsRelation()->sync($this->buildCarPivotPayload($carIds));

        return redirect()->route('admin.car-with-drivers')->with('success', 'Car with driver updated successfully.');
    }

    public function destroy(int $id)
    {
        $record = CarWithDriver::find($id);

        if (!$record) {
            return back()->with('error', 'Car with driver record not found.');
        }

        $record->deleted_by = Auth::id();
        $record->save();
        $record->delete();

        return redirect()->route('admin.car-with-drivers')->with('success', 'Car with driver deleted successfully.');
    }

    public function restore(int $id)
    {
        $record = CarWithDriver::withTrashed()->find($id);

        if (!$record) {
            return back()->with('error', 'Car with driver record not found.');
        }

        if (is_null($record->deleted_at)) {
            return back()->with('error', 'Car with driver record is not deleted.');
        }

        $record->restore();
        $record->deleted_by = null;
        $record->save();

        return redirect()->route('admin.car-with-drivers')->with('success', 'Car with driver restored successfully.');
    }

    private function listRecords(Request $request, bool $onlyMine = false)
    {
        $perPage = (int) $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->boolean('is_export');

        $query = $isDeleted
            ? CarWithDriver::onlyTrashed()->with(['carsRelation', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            : CarWithDriver::with(['carsRelation', 'createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('display_en', 'LIKE', "%{$search}%")
                    ->orWhere('display_ar', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('header_en', 'LIKE', "%{$search}%")
                    ->orWhere('header_ar', 'LIKE', "%{$search}%")
                    ->orWhere('meta_title_en', 'LIKE', "%{$search}%")
                    ->orWhere('meta_title_ar', 'LIKE', "%{$search}%")
                    ->orWhereHas('carsRelation', function ($carQuery) use ($search) {
                        $carQuery->where('name_en', 'LIKE', "%{$search}%")
                            ->orWhere('name_ar', 'LIKE', "%{$search}%")
                            ->orWhere('model', 'LIKE', "%{$search}%");
                    });
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->exportRecords($query, $isDeleted);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $records->getCollection()->map(function (CarWithDriver $record) use ($authUser) {
                return [
                    'id' => $record->id,
                    'display_en' => $record->display_en,
                    'display_ar' => $record->display_ar,
                    'slug' => $record->slug,
                    'header_en' => $record->header_en,
                    'car_image_url' => $record->card_image_url,
                    'car_names' => $record->carsRelation->pluck('name_en')->values()->all(),
                    'deleted_at' => optional($record->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($record->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.car-with-drivers.show', $record->id),
                    'edit_url' => route('admin.car-with-drivers.edit', $record->id),
                    'delete_url' => route('admin.car-with-drivers.delete', $record->id),
                    'restore_url' => route('admin.car-with-drivers.restore', $record->id),
                    'permissions' => [
                        'can_view' => $authUser->can('CarWithDriver_ViewAll') || $authUser->can('CarWithDriver_ViewMine') || $authUser->can('CarWithDriver_View'),
                        'can_edit' => $authUser->can('CarWithDriver_Edit'),
                        'can_delete' => $authUser->can('CarWithDriver_Delete'),
                        'can_restore' => $authUser->can('CarWithDriver_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My car with driver records fetched successfully.' : 'Car with driver records fetched successfully.',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $records->currentPage(),
                        'last_page' => $records->lastPage(),
                        'per_page' => $records->perPage(),
                        'total' => $records->total(),
                        'from' => $records->firstItem(),
                        'to' => $records->lastItem(),
                        'has_more_pages' => $records->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.car-with-drivers.index');
    }

    private function validationRules(?int $id = null): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('car_with_drivers', 'slug')->ignore($id)],
            'display_en' => ['required', 'string'],
            'display_ar' => ['required', 'string'],
            'meta_title_en' => ['required', 'string'],
            'meta_description_en' => ['required', 'string'],
            'meta_title_ar' => ['required', 'string'],
            'meta_description_ar' => ['required', 'string'],
            'card_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'card_header_en' => ['nullable', 'string'],
            'card_text_en' => ['nullable', 'string'],
            'card_header_ar' => ['nullable', 'string'],
            'card_text_ar' => ['nullable', 'string'],
            'header_en' => ['required', 'string'],
            'header_ar' => ['required', 'string'],
            'content_en' => ['required', 'string'],
            'content_ar' => ['required', 'string'],
            'car_ids' => ['nullable', 'array'],
            'car_ids.*' => ['integer', Rule::exists('cars', 'id')],
        ];
    }

    private function buildPayload(array $validated, Request $request, ?CarWithDriver $record = null): array
    {
        return [
            'display_en' => $validated['display_en'],
            'display_ar' => $validated['display_ar'],
            'meta_title_en' => $validated['meta_title_en'],
            'meta_description_en' => $validated['meta_description_en'],
            'meta_title_ar' => $validated['meta_title_ar'],
            'meta_description_ar' => $validated['meta_description_ar'],
            'card_image' => $request->hasFile('card_image') ? $this->storeImage($request->file('card_image')) : ($record->card_image ?? null),
            'card_header_en' => $validated['card_header_en'] ?? null,
            'card_text_en' => $validated['card_text_en'] ?? null,
            'card_header_ar' => $validated['card_header_ar'] ?? null,
            'card_text_ar' => $validated['card_text_ar'] ?? null,
            'header_en' => $validated['header_en'],
            'header_ar' => $validated['header_ar'],
            'content_en' => $validated['content_en'],
            'content_ar' => $validated['content_ar'],
        ];
    }

    private function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source) ?: 'car-with-driver';
        $slug = $baseSlug;
        $counter = 1;

        while (CarWithDriver::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeImage($file): string
    {
        $folder = 'car-with-drivers/' . now()->format('FY') . '/card';
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeCarIds(array $carIds): array
    {
        return collect($carIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function encodeCarIds(array $carIds): ?string
    {
        return empty($carIds) ? null : json_encode($carIds);
    }

    private function buildCarPivotPayload(array $carIds): array
    {
        return collect($carIds)
            ->mapWithKeys(fn ($carId) => [$carId => ['created_by' => Auth::id(), 'updated_by' => Auth::id()]])
            ->all();
    }

    private function exportRecords($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Display EN', 'Display AR', 'Header EN', 'Slug', 'Cars', 'Created At'];

            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
                    $record->display_en,
                    $record->display_ar,
                    $record->header_en,
                    $record->slug,
                    $record->carsRelation->pluck('name_en')->implode(', '),
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($record->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=car-with-drivers.csv',
        ]);
    }
}
