<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Car_ViewAll|Car_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Car_ViewAll|Car_ViewMine|Car_View', ['only' => ['showCar']]);
        $this->middleware('permission:Car_Add', ['only' => ['createCar', 'postCar']]);
        $this->middleware('permission:Car_Edit', ['only' => ['editCar', 'updateCar']]);
        $this->middleware('permission:Car_Delete', ['only' => ['deleteCar']]);
        $this->middleware('permission:Car_Revoke', ['only' => ['revokeCar']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Car_ViewAll')) {
            return $this->getCars($request);
        }

        if ($user->can('Car_ViewMine')) {
            return $this->getMyCars($request);
        }

        abort(403, 'You do not have permission to view cars.');
    }

    public function getCars(Request $request)
    {
        return $this->listCars($request);
    }

    public function getMyCars(Request $request)
    {
        return $this->listCars($request, true);
    }

    public function createCar()
    {
        return view('admin.cars.create', [
            'brands' => Brand::orderedForListing()->get(),
            'categories' => Category::orderBy('name_en')->get(),
        ]);
    }

    public function postCar(Request $request)
    {
        $validated = $request->validate($this->validationRules($request));
        $car = null;

        DB::transaction(function () use ($validated, $request, &$car) {
            $car = Car::create($this->buildPayload($validated, $request) + [
                'slug' => $this->generateUniqueSlug($validated['slug'] ?? $validated['name_en']),
                'created_by' => Auth::id(),
            ]);

            $this->placeCarInBrandSorting($car, (int) $validated['brand_id'], $this->resolveSortingValue($validated));
            $car->categories()->sync($validated['category_ids'] ?? []);
        });

        return redirect()->route('admin.cars')->with('success', 'Car added successfully.');
    }

    public function showCar($id)
    {
        $car = Car::withTrashed()
            ->with(['brand', 'categories', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            ->find($id);

        if (!$car) {
            return redirect()->route('admin.cars')->with('error', 'Car not found.');
        }

        return view('admin.cars.show', compact('car'));
    }

    public function editCar($id)
    {
        $car = Car::with(['brand', 'categories'])->find($id);

        if (!$car) {
            return redirect()->route('admin.cars')->with('error', 'Car not found.');
        }

        return view('admin.cars.edit', [
            'car' => $car,
            'brands' => Brand::orderedForListing()->get(),
            'categories' => Category::orderBy('name_en')->get(),
        ]);
    }

    public function getSortOrders(int $brandId, Request $request)
    {
        $ignoreCarId = $request->integer('ignore_car_id');

        $orders = Car::query()
            ->where('brand_id', $brandId)
            ->when($ignoreCarId, fn ($query) => $query->where('id', '!=', $ignoreCarId))
            ->whereNotNull('sorting')
            ->orderBy('sorting')
            ->pluck('sorting')
            ->map(fn ($sorting) => (int) $sorting)
            ->values()
            ->all();

        return response()->json($orders);
    }

    public function getFeaturedSortOrders(Request $request)
    {
        $ignoreCarId = $request->integer('ignore_car_id');

        $orders = Car::query()
            ->where('featured', 1)
            ->when($ignoreCarId, fn ($query) => $query->where('id', '!=', $ignoreCarId))
            ->whereNotNull('featured_sorting')
            ->orderBy('featured_sorting')
            ->pluck('featured_sorting')
            ->map(fn ($sorting) => (int) $sorting)
            ->values()
            ->all();

        return response()->json($orders);
    }

    public function updateCar(Request $request, $id)
    {
        $car = Car::with('categories')->find($id);

        if (!$car) {
            return redirect()->route('admin.cars')->with('error', 'Car not found.');
        }

        $validated = $request->validate($this->validationRules($request, $car->id));

        DB::transaction(function () use ($request, $validated, $car) {
            $originalBrandId = (int) $car->brand_id;
            $originalSorting = $car->sorting !== null ? (int) $car->sorting : null;

            if ($request->hasFile('main_image')) {
                $this->deleteImage($car->main_image);
            }

            if ($request->hasFile('images')) {
                $this->deleteImages($car->images ?? []);
            }

            $originalFeatured = (bool) $car->featured;
            $originalFeaturedSorting = $car->featured_sorting !== null ? (int) $car->featured_sorting : null;

            $car->fill($this->buildPayload($validated, $request, $car));
            $car->slug = $this->generateUniqueSlug($validated['slug'] ?? $validated['name_en'], $car->id);
            $car->updated_by = Auth::id();
            $car->save();

            $this->placeCarInBrandSorting(
                $car,
                (int) $validated['brand_id'],
                $this->resolveSortingValue($validated, $car),
                $originalBrandId,
                $originalSorting,
            );

            $this->placeCarInFeaturedSorting(
                $car,
                (bool) ($validated['featured'] ?? 0),
                $this->resolveFeaturedSortingValue($validated, $car),
                $originalFeatured,
                $originalFeaturedSorting,
            );

            $car->categories()->sync($validated['category_ids'] ?? []);
        });

        return redirect()->route('admin.cars')->with('success', 'Car updated successfully.');
    }

    public function deleteCar($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return back()->with('error', 'Car not found.');
        }

        DB::transaction(function () use ($car) {
            $brandId = $car->brand_id ? (int) $car->brand_id : null;
            $sorting = $car->sorting !== null ? (int) $car->sorting : null;

            $car->deleted_by = Auth::id();
            $car->save();
            $car->delete();

            if ($brandId !== null && $sorting !== null) {
                $this->closeBrandSortingGap($brandId, $sorting, $car->id);
            }

            if ($car->featured && $car->featured_sorting !== null) {
                $this->closeFeaturedSortingGap((int) $car->featured_sorting, $car->id);
            }
        });

        return redirect()->route('admin.cars')->with('success', 'Car deleted successfully.');
    }

    public function revokeCar($id)
    {
        $car = Car::withTrashed()->find($id);

        if (!$car) {
            return back()->with('error', 'Car not found.');
        }

        if (is_null($car->deleted_at)) {
            return back()->with('error', 'Car is not deleted.');
        }

        DB::transaction(function () use ($car) {
            $car->restore();
            $car->deleted_by = null;
            $car->save();

            if ($car->brand_id !== null) {
                $this->placeCarInBrandSorting(
                    $car,
                    (int) $car->brand_id,
                    Car::nextSortingForBrand((int) $car->brand_id, $car->id),
                );
            }

            if ($car->featured) {
                $this->placeCarInFeaturedSorting(
                    $car,
                    true,
                    Car::nextFeaturedSorting($car->id),
                );
            }
        });

        return redirect()->route('admin.cars')->with('success', 'Car restored successfully.');
    }

    private function listCars(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Car::onlyTrashed()->with(['brand', 'categories', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            : Car::with(['brand', 'categories', 'createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_en', 'LIKE', "%{$search}%")
                    ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name_en', 'LIKE', "%{$search}%")->orWhere('name_ar', 'LIKE', "%{$search}%"))
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name_en', 'LIKE', "%{$search}%")->orWhere('name_ar', 'LIKE', "%{$search}%"));
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->exportCars($query, $isDeleted);
        }

        $cars = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $cars->getCollection()->map(function (Car $car) use ($authUser) {
                return [
                    'id' => $car->id,
                    'name_en' => $car->name_en,
                    'name_ar' => $car->name_ar,
                    'slug' => $car->slug,
                    'model' => $car->model,
                    'brand_name' => $car->brand?->name_en,
                    'category_names' => $car->categories->pluck('name_en')->values()->all(),
                    'main_image_url' => $car->main_image_url,
                    'price_daily' => $car->price_daily,
                    'price_weekly' => $car->price_weekly,
                    'price_monthly' => $car->price_monthly,
                    'stock' => $car->stock,
                    'featured' => (bool) $car->featured,
                    'deleted_at' => optional($car->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($car->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($car, $authUser),
                    'show_url' => route('admin.cars.show', $car->id),
                    'edit_url' => route('admin.cars.edit', $car->id),
                    'delete_url' => route('admin.cars.delete', $car->id),
                    'restore_url' => route('admin.cars.revoke', $car->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Car_ViewAll') || $authUser->can('Car_ViewMine') || $authUser->can('Car_View'),
                        'can_edit' => $authUser->can('Car_Edit'),
                        'can_delete' => $authUser->can('Car_Delete'),
                        'can_restore' => $authUser->can('Car_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My cars fetched successfully.' : 'Cars fetched successfully.',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $cars->currentPage(),
                        'last_page' => $cars->lastPage(),
                        'per_page' => $cars->perPage(),
                        'total' => $cars->total(),
                        'from' => $cars->firstItem(),
                        'to' => $cars->lastItem(),
                        'has_more_pages' => $cars->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.cars.index');
    }

    private function validationRules(Request $request, ?int $carId = null): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'price_daily' => ['required', 'string', 'max:255'],
            'price_weekly' => ['required', 'string', 'max:255'],
            'price_monthly' => ['required', 'string', 'max:255'],
            'full_insurance_amount' => ['nullable', 'string', 'max:255'],
            'additional_driver_amount' => ['nullable', 'string', 'max:255'],
            'baby_seat_amount' => ['nullable', 'string', 'max:255'],
            'deposit_amount' => ['nullable', 'string', 'max:255'],
            'waiver_amount' => ['nullable', 'string', 'max:255'],
            'different_city_dropoff_fee' => ['nullable', 'string', 'max:255'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'model' => ['required', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'featured_sorting' => ['nullable', 'integer', 'min:0'],
            'engine' => ['nullable', 'string', 'max:255'],
            'seats' => ['nullable', 'string', 'max:255'],
            'doors' => ['nullable', 'string', 'max:255'],
            'deposit' => ['nullable', 'string', 'max:255'],
            'luggage' => ['nullable', 'string', 'max:255'],
            'cruise_control' => ['nullable', 'boolean'],
            'bluetooth' => ['nullable', 'boolean'],
            'automatic' => ['nullable', 'boolean'],
            'parking_sensor' => ['nullable', 'boolean'],
            'navigation' => ['nullable', 'boolean'],
            'carplay' => ['nullable', 'boolean'],
            'camera' => ['nullable', 'boolean'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('cars', 'slug')->ignore($carId)],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_brief_en' => ['nullable', 'string'],
            'seo_brief_ar' => ['nullable', 'string'],
            'brand_id' => ['required', 'integer', Rule::exists('brands', 'id')],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')],
            'stock' => ['required', 'boolean'],
            'cdw_daily' => ['nullable', 'string', 'max:255'],
            'cdw_weekly' => ['nullable', 'string', 'max:255'],
            'cdw_monthly' => ['nullable', 'string', 'max:255'],
            'sorting' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function buildPayload(array $validated, Request $request, ?Car $car = null): array
    {
        return [
            'name_en' => $validated['name_en'],
            'name_ar' => $validated['name_ar'],
            'description_en' => $validated['description_en'] ?? null,
            'description_ar' => $validated['description_ar'] ?? null,
            'price_daily' => $validated['price_daily'],
            'price_weekly' => $validated['price_weekly'],
            'price_monthly' => $validated['price_monthly'],
            'full_insurance_amount' => $validated['full_insurance_amount'] ?? null,
            'additional_driver_amount' => $validated['additional_driver_amount'] ?? null,
            'baby_seat_amount' => $validated['baby_seat_amount'] ?? null,
            'deposit_amount' => $validated['deposit_amount'] ?? null,
            'waiver_amount' => $validated['waiver_amount'] ?? null,
            'different_city_dropoff_fee' => $validated['different_city_dropoff_fee'] ?? null,
            'main_image' => $request->hasFile('main_image') ? $this->storeSingleImage($request->file('main_image'), 'main') : ($car->main_image ?? null),
            'images' => $request->hasFile('images') ? $this->storeMultipleImages($request->file('images'), 'gallery') : ($car->images ?? []),
            'model' => $validated['model'],
            'featured' => (int) ($validated['featured'] ?? 0),
            'featured_sorting' => $validated['featured'] ?? 0 ? $this->resolveFeaturedSortingValue($validated, $car) : null,
            'engine' => $validated['engine'] ?? null,
            'seats' => $validated['seats'] ?? null,
            'doors' => $validated['doors'] ?? null,
            'deposit' => $validated['deposit'] ?? null,
            'luggage' => $validated['luggage'] ?? null,
            'cruise_control' => (int) ($validated['cruise_control'] ?? 0),
            'bluetooth' => (int) ($validated['bluetooth'] ?? 0),
            'automatic' => (int) ($validated['automatic'] ?? 0),
            'parking_sensor' => (int) ($validated['parking_sensor'] ?? 0),
            'navigation' => (int) ($validated['navigation'] ?? 0),
            'carplay' => (int) ($validated['carplay'] ?? 0),
            'camera' => (int) ($validated['camera'] ?? 0),
            'seo_title_en' => $validated['seo_title_en'] ?? null,
            'seo_title_ar' => $validated['seo_title_ar'] ?? null,
            'seo_brief_en' => $validated['seo_brief_en'] ?? null,
            'seo_brief_ar' => $validated['seo_brief_ar'] ?? null,
            'brand_id' => $validated['brand_id'],
            'stock' => (int) ($validated['stock'] ?? 0),
            'cdw_daily' => $validated['cdw_daily'] ?? null,
            'cdw_weekly' => $validated['cdw_weekly'] ?? null,
            'cdw_monthly' => $validated['cdw_monthly'] ?? null,
        ];
    }

    private function resolveSortingValue(array $validated, ?Car $car = null): int
    {
        if (!blank($validated['sorting'] ?? null)) {
            return (int) $validated['sorting'];
        }

        return Car::nextSortingForBrand((int) $validated['brand_id'], $car?->id);
    }

    private function resolveFeaturedSortingValue(array $validated, ?Car $car = null): ?int
    {
        if (!($validated['featured'] ?? 0)) {
            return null;
        }

        if (!blank($validated['featured_sorting'] ?? null)) {
            return (int) $validated['featured_sorting'];
        }

        return Car::nextFeaturedSorting($car?->id);
    }

    private function placeCarInBrandSorting(
        Car $car,
        int $targetBrandId,
        int $targetSorting,
        ?int $originalBrandId = null,
        ?int $originalSorting = null
    ): void {
        if ($originalBrandId !== null && $originalSorting !== null) {
            if ($originalBrandId === $targetBrandId) {
                $this->moveWithinBrandSorting($car, $targetBrandId, $originalSorting, $targetSorting);
                return;
            }

            $this->closeBrandSortingGap($originalBrandId, $originalSorting, $car->id);
        }

        $this->insertIntoBrandSorting($car, $targetBrandId, $targetSorting);
    }

    private function moveWithinBrandSorting(Car $car, int $brandId, int $fromSorting, int $toSorting): void
    {
        if ($fromSorting === $toSorting) {
            $car->forceFill(['sorting' => $toSorting])->saveQuietly();
            return;
        }

        if ($toSorting < $fromSorting) {
            Car::query()
                ->where('brand_id', $brandId)
                ->where('id', '!=', $car->id)
                ->whereBetween('sorting', [$toSorting, $fromSorting - 1])
                ->increment('sorting');
        } else {
            Car::query()
                ->where('brand_id', $brandId)
                ->where('id', '!=', $car->id)
                ->whereBetween('sorting', [$fromSorting + 1, $toSorting])
                ->decrement('sorting');
        }

        $car->forceFill(['sorting' => $toSorting])->saveQuietly();
    }

    private function insertIntoBrandSorting(Car $car, int $brandId, int $targetSorting): void
    {
        Car::query()
            ->where('brand_id', $brandId)
            ->where('id', '!=', $car->id)
            ->where('sorting', '>=', $targetSorting)
            ->increment('sorting');

        $car->forceFill([
            'brand_id' => $brandId,
            'sorting' => $targetSorting,
        ])->saveQuietly();
    }

    private function closeBrandSortingGap(int $brandId, int $fromSorting, int $ignoreCarId): void
    {
        Car::query()
            ->where('brand_id', $brandId)
            ->where('id', '!=', $ignoreCarId)
            ->where('sorting', '>', $fromSorting)
            ->decrement('sorting');
    }

    private function placeCarInFeaturedSorting(
        Car $car,
        bool $shouldBeFeatured,
        ?int $targetSorting,
        ?bool $wasFeatured = null,
        ?int $originalFeaturedSorting = null
    ): void {
        if (!$shouldBeFeatured) {
            if ($wasFeatured && $originalFeaturedSorting !== null) {
                $this->closeFeaturedSortingGap($originalFeaturedSorting, $car->id);
            }

            $car->forceFill(['featured_sorting' => null])->saveQuietly();
            return;
        }

        if ($wasFeatured && $originalFeaturedSorting !== null && $targetSorting !== null) {
            $this->moveWithinFeaturedSorting($car, $originalFeaturedSorting, $targetSorting);
            return;
        }

        if ($targetSorting !== null) {
            $this->insertIntoFeaturedSorting($car, $targetSorting);
        }
    }

    private function moveWithinFeaturedSorting(Car $car, int $fromSorting, int $toSorting): void
    {
        if ($fromSorting === $toSorting) {
            $car->forceFill(['featured_sorting' => $toSorting])->saveQuietly();
            return;
        }

        if ($toSorting < $fromSorting) {
            Car::query()
                ->where('featured', 1)
                ->where('id', '!=', $car->id)
                ->whereBetween('featured_sorting', [$toSorting, $fromSorting - 1])
                ->increment('featured_sorting');
        } else {
            Car::query()
                ->where('featured', 1)
                ->where('id', '!=', $car->id)
                ->whereBetween('featured_sorting', [$fromSorting + 1, $toSorting])
                ->decrement('featured_sorting');
        }

        $car->forceFill(['featured_sorting' => $toSorting])->saveQuietly();
    }

    private function insertIntoFeaturedSorting(Car $car, int $targetSorting): void
    {
        Car::query()
            ->where('featured', 1)
            ->where('id', '!=', $car->id)
            ->where('featured_sorting', '>=', $targetSorting)
            ->increment('featured_sorting');

        $car->forceFill(['featured_sorting' => $targetSorting])->saveQuietly();
    }

    private function closeFeaturedSortingGap(int $fromSorting, int $ignoreCarId): void
    {
        Car::query()
            ->where('featured', 1)
            ->where('id', '!=', $ignoreCarId)
            ->where('featured_sorting', '>', $fromSorting)
            ->decrement('featured_sorting');
    }

    private function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = $this->normalizeSlug($source);
        $baseSlug = $baseSlug ?: 'car';
        $slug = $baseSlug;
        $counter = 1;

        while (Car::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeSingleImage($file, string $segment): string
    {
        $folder = 'cars/' . now()->format('FY') . '/' . $segment;
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function normalizeSlug(string $source): string
    {
        $source = strtolower(trim($source));
        $source = preg_replace('/[^a-z0-9-]+/', '-', $source) ?? '';
        $source = preg_replace('/-+/', '-', $source) ?? '';

        return trim($source, '-');
    }

    private function storeMultipleImages(array $files, string $segment): array
    {
        return collect($files)
            ->map(fn ($file) => $this->storeSingleImage($file, $segment))
            ->values()
            ->all();
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function deleteImages(array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteImage($path);
        }
    }

    private function exportCars($query, bool $isDeleted)
    {
        $cars = $query->get();

        return response()->stream(function () use ($cars, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Name EN', 'Name AR', 'Brand', 'Model', 'Slug', 'Price Daily', 'Price Weekly', 'Price Monthly', 'Stock', 'Featured', 'Created At'];

            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($cars as $car) {
                $row = [
                    $car->id,
                    $car->name_en,
                    $car->name_ar,
                    $car->brand?->name_en,
                    $car->model,
                    $car->slug,
                    $car->price_daily,
                    $car->price_weekly,
                    $car->price_monthly,
                    $car->stock,
                    $car->featured ? 'Yes' : 'No',
                    optional($car->created_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($car->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=cars.csv',
        ]);
    }
}

