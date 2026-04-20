<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Brand_ViewAll|Brand_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Brand_ViewAll|Brand_ViewMine|Brand_View', ['only' => ['show']]);
        $this->middleware('permission:Brand_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Brand_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Brand_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Brand_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Brand_ViewAll')) {
            return $this->getBrands($request);
        }

        if ($user->can('Brand_ViewMine')) {
            return $this->getMyBrands($request);
        }

        abort(403, 'You do not have permission to view brands.');
    }

    public function getBrands(Request $request)
    {
        return $this->listBrands($request);
    }

    public function getMyBrands(Request $request)
    {
        return $this->listBrands($request, true);
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function getSortOrders(Request $request)
    {
        $ignoreBrandId = $request->integer('ignore_brand_id');

        $orders = Brand::query()
            ->when($ignoreBrandId, fn ($query) => $query->where('id', '!=', $ignoreBrandId))
            ->whereNotNull('sorting')
            ->orderBy('sorting')
            ->pluck('sorting')
            ->map(fn ($sorting) => (int) $sorting)
            ->values()
            ->all();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBrand($request);
        DB::transaction(function () use ($request, $validated) {
            $brand = Brand::create([
                'name_en' => $validated['name_en'],
                'name_ar' => $validated['name_ar'] ?? null,
                'description_en' => $validated['description_en'] ?? null,
                'description_ar' => $validated['description_ar'] ?? null,
                'seo_title_en' => $validated['seo_title_en'] ?? null,
                'seo_title_ar' => $validated['seo_title_ar'] ?? null,
                'seo_brief_en' => $validated['seo_brief_en'] ?? null,
                'seo_brief_ar' => $validated['seo_brief_ar'] ?? null,
                'slug' => Str::slug($validated['slug']),
                'logo' => $this->storeLogo($request),
                'created_by' => Auth::id(),
            ]);

            $this->insertIntoBrandSorting($brand, $this->resolveSortingValue($validated));
        });

        return redirect()->route('admin.brands')->with('success', 'Brand created successfully.');
    }

    public function show(int $id)
    {
        $brand = Brand::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found.');
        }

        return view('admin.brands.show', compact('brand'));
    }

    public function edit(int $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found.');
        }

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, int $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found.');
        }

        $validated = $this->validateBrand($request, $brand->id);

        DB::transaction(function () use ($request, $validated, $brand) {
            $originalSorting = $brand->sorting !== null ? (int) $brand->sorting : null;

            if ($request->hasFile('logo')) {
                $this->deleteLogo($brand->logo);
                $brand->logo = $this->storeLogo($request);
            }

            $brand->fill([
                'name_en' => $validated['name_en'],
                'name_ar' => $validated['name_ar'] ?? null,
                'description_en' => $validated['description_en'] ?? null,
                'description_ar' => $validated['description_ar'] ?? null,
                'seo_title_en' => $validated['seo_title_en'] ?? null,
                'seo_title_ar' => $validated['seo_title_ar'] ?? null,
                'seo_brief_en' => $validated['seo_brief_en'] ?? null,
                'seo_brief_ar' => $validated['seo_brief_ar'] ?? null,
                'slug' => Str::slug($validated['slug']),
                'updated_by' => Auth::id(),
            ]);
            $brand->save();

            $this->placeBrandInSorting($brand, $this->resolveSortingValue($validated, $brand), $originalSorting);
        });

        return redirect()->route('admin.brands')->with('success', 'Brand updated successfully.');
    }

    public function destroy(int $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return back()->with('error', 'Brand not found.');
        }

        DB::transaction(function () use ($brand) {
            $sorting = $brand->sorting !== null ? (int) $brand->sorting : null;

            $brand->deleted_by = Auth::id();
            $brand->save();
            $brand->delete();

            if ($sorting !== null) {
                $this->closeBrandSortingGap($sorting, $brand->id);
            }
        });

        return redirect()->route('admin.brands')->with('success', 'Brand deleted successfully.');
    }

    public function restore(int $id)
    {
        $brand = Brand::withTrashed()->find($id);

        if (!$brand) {
            return back()->with('error', 'Brand not found.');
        }

        if (is_null($brand->deleted_at)) {
            return back()->with('error', 'Brand is not deleted.');
        }

        DB::transaction(function () use ($brand) {
            $brand->restore();
            $brand->deleted_by = null;
            $brand->save();
            $this->insertIntoBrandSorting($brand, Brand::nextSorting($brand->id));
        });

        return redirect()->route('admin.brands')->with('success', 'Brand restored successfully.');
    }

    private function listBrands(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Brand::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Brand::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_en', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_ar', 'LIKE', "%{$search}%");
            });
        }

        $query->orderedForListing();

        if ($isExport) {
            return $this->export($query, $isDeleted);
        }

        $brands = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $brands->getCollection()->map(function (Brand $brand) {
                $authUser = Auth::user();

                return [
                    'id' => $brand->id,
                    'name_en' => $brand->name_en,
                    'name_ar' => $brand->name_ar,
                    'slug' => $brand->slug,
                    'seo_title_en' => $brand->seo_title_en,
                    'logo_url' => $brand->logo_url,
                    'sorting' => $brand->sorting,
                    'deleted_at' => optional($brand->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($brand->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($brand, $authUser),
                    'show_url' => route('admin.brands.show', $brand->id),
                    'edit_url' => route('admin.brands.edit', $brand->id),
                    'delete_url' => route('admin.brands.delete', $brand->id),
                    'restore_url' => route('admin.brands.restore', $brand->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Brand_ViewAll') || $authUser->can('Brand_ViewMine') || $authUser->can('Brand_View'),
                        'can_edit' => $authUser->can('Brand_Edit'),
                        'can_delete' => $authUser->can('Brand_Delete'),
                        'can_restore' => $authUser->can('Brand_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My brands fetched successfully.' : 'Brands fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $brands->currentPage(),
                        'last_page' => $brands->lastPage(),
                        'per_page' => $brands->perPage(),
                        'total' => $brands->total(),
                        'from' => $brands->firstItem(),
                        'to' => $brands->lastItem(),
                        'has_more_pages' => $brands->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.brands.index');
    }

    private function validateBrand(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_brief_en' => ['nullable', 'string'],
            'seo_brief_ar' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($id)],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sorting' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function storeLogo(Request $request): ?string
    {
        if (!$request->hasFile('logo')) {
            return null;
        }

        $file = $request->file('logo');
        $folder = 'brands/' . now()->format('FY');
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function deleteLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function export($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Sort Order', 'Name EN', 'Name AR', 'Slug', 'SEO Title EN', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
                    $record->sorting,
                    $record->name_en,
                    $record->name_ar,
                    $record->slug,
                    $record->seo_title_en,
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
            'Content-Disposition' => 'attachment; filename=brands.csv',
        ]);
    }

    private function resolveSortingValue(array $validated, ?Brand $brand = null): int
    {
        if (!blank($validated['sorting'] ?? null)) {
            return (int) $validated['sorting'];
        }

        return Brand::nextSorting($brand?->id);
    }

    private function placeBrandInSorting(Brand $brand, int $targetSorting, ?int $originalSorting = null): void
    {
        if ($originalSorting !== null) {
            $this->moveWithinBrandSorting($brand, $originalSorting, $targetSorting);
            return;
        }

        $this->insertIntoBrandSorting($brand, $targetSorting);
    }

    private function moveWithinBrandSorting(Brand $brand, int $fromSorting, int $toSorting): void
    {
        if ($fromSorting === $toSorting) {
            $brand->forceFill(['sorting' => $toSorting])->saveQuietly();
            return;
        }

        if ($toSorting < $fromSorting) {
            Brand::query()
                ->where('id', '!=', $brand->id)
                ->whereBetween('sorting', [$toSorting, $fromSorting - 1])
                ->increment('sorting');
        } else {
            Brand::query()
                ->where('id', '!=', $brand->id)
                ->whereBetween('sorting', [$fromSorting + 1, $toSorting])
                ->decrement('sorting');
        }

        $brand->forceFill(['sorting' => $toSorting])->saveQuietly();
    }

    private function insertIntoBrandSorting(Brand $brand, int $targetSorting): void
    {
        Brand::query()
            ->where('id', '!=', $brand->id)
            ->where('sorting', '>=', $targetSorting)
            ->increment('sorting');

        $brand->forceFill(['sorting' => $targetSorting])->saveQuietly();
    }

    private function closeBrandSortingGap(int $fromSorting, int $ignoreBrandId): void
    {
        Brand::query()
            ->where('id', '!=', $ignoreBrandId)
            ->where('sorting', '>', $fromSorting)
            ->decrement('sorting');
    }
}

