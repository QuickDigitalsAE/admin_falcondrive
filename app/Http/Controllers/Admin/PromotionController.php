<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Promotion_ViewAll|Promotion_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Promotion_ViewAll|Promotion_ViewMine|Promotion_View', ['only' => ['show']]);
        $this->middleware('permission:Promotion_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Promotion_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Promotion_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Promotion_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Promotion_ViewAll')) {
            return $this->listPromotions($request);
        }

        if ($user->can('Promotion_ViewMine')) {
            return $this->listPromotions($request, true);
        }

        abort(403, 'You do not have permission to view promotions.');
    }

    public function create()
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        Promotion::create([
            'name_en' => $validated['name_en'],
            'name_ar' => $validated['name_ar'],
            'description_en' => $validated['description_en'] ?? null,
            'description_ar' => $validated['description_ar'] ?? null,
            'seo_title_en' => $validated['seo_title_en'],
            'seo_title_ar' => $validated['seo_title_ar'],
            'seo_brief_en' => $validated['seo_brief_en'],
            'seo_brief_ar' => $validated['seo_brief_ar'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?? $validated['name_en']),
            'image' => $this->storeImage($request),
            'top_offer' => (int) ($validated['top_offer'] ?? 0),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.promotions')->with('success', 'Promotion added successfully.');
    }

    public function show(int $id)
    {
        $promotion = Promotion::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$promotion) {
            return redirect()->route('admin.promotions')->with('error', 'Promotion not found.');
        }

        return view('admin.promotions.show', compact('promotion'));
    }

    public function edit(int $id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return back()->with('error', 'Promotion not found.');
        }

        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, int $id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return back()->with('error', 'Promotion not found.');
        }

        $validated = $request->validate($this->validationRules($id));

        if ($request->hasFile('image')) {
            $this->deleteImage($promotion->image);
            $promotion->image = $this->storeImage($request);
        }

        $promotion->name_en = $validated['name_en'];
        $promotion->name_ar = $validated['name_ar'];
        $promotion->description_en = $validated['description_en'] ?? null;
        $promotion->description_ar = $validated['description_ar'] ?? null;
        $promotion->seo_title_en = $validated['seo_title_en'];
        $promotion->seo_title_ar = $validated['seo_title_ar'];
        $promotion->seo_brief_en = $validated['seo_brief_en'];
        $promotion->seo_brief_ar = $validated['seo_brief_ar'];
        $promotion->slug = $this->generateUniqueSlug($validated['slug'] ?? $validated['name_en'], $promotion->id);
        $promotion->top_offer = (int) ($validated['top_offer'] ?? 0);
        $promotion->updated_by = Auth::id();
        $promotion->save();

        return redirect()->route('admin.promotions')->with('success', 'Promotion updated successfully.');
    }

    public function destroy(int $id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return back()->with('error', 'Promotion not found.');
        }

        $promotion->deleted_by = Auth::id();
        $promotion->save();
        $promotion->delete();

        return redirect()->route('admin.promotions')->with('success', 'Promotion deleted successfully.');
    }

    public function restore(int $id)
    {
        $promotion = Promotion::withTrashed()->find($id);

        if (!$promotion) {
            return back()->with('error', 'Promotion not found.');
        }

        if (is_null($promotion->deleted_at)) {
            return back()->with('error', 'Promotion is not deleted.');
        }

        $promotion->restore();
        $promotion->deleted_by = null;
        $promotion->save();

        return redirect()->route('admin.promotions')->with('success', 'Promotion restored successfully.');
    }

    private function listPromotions(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted ? Promotion::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser']) : Promotion::with(['createdByUser', 'updatedByUser']);

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

        $query->latest('id');

        if ($isExport) {
            return $this->exportPromotions($query, $isDeleted);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $records->getCollection()->map(function (Promotion $promotion) use ($authUser) {
                return [
                    'id' => $promotion->id,
                    'name_en' => $promotion->name_en,
                    'name_ar' => $promotion->name_ar,
                    'slug' => $promotion->slug,
                    'image_url' => $promotion->image_url,
                    'top_offer' => (bool) $promotion->top_offer,
                    'deleted_at' => optional($promotion->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($promotion->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($promotion, $authUser),
                    'show_url' => route('admin.promotions.show', $promotion->id),
                    'edit_url' => route('admin.promotions.edit', $promotion->id),
                    'delete_url' => route('admin.promotions.delete', $promotion->id),
                    'restore_url' => route('admin.promotions.restore', $promotion->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Promotion_ViewAll') || $authUser->can('Promotion_ViewMine') || $authUser->can('Promotion_View'),
                        'can_edit' => $authUser->can('Promotion_Edit'),
                        'can_delete' => $authUser->can('Promotion_Delete'),
                        'can_restore' => $authUser->can('Promotion_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My promotions fetched successfully.' : 'Promotions fetched successfully.',
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
                    'filters' => ['search' => $search, 'is_deleted' => $isDeleted],
                ],
            ]);
        }

        return view('admin.promotions.index');
    }

    private function validationRules(?int $id = null): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'seo_title_en' => ['required', 'string', 'max:255'],
            'seo_title_ar' => ['required', 'string', 'max:255'],
            'seo_brief_en' => ['required', 'string'],
            'seo_brief_ar' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('promotions', 'slug')->ignore($id)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'top_offer' => ['nullable', 'boolean'],
        ];
    }

    private function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = $this->normalizeSlug($source) ?: 'promotion';
        $slug = $baseSlug;
        $counter = 1;

        while (Promotion::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $folder = 'promotions/' . now()->format('FY');
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

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function exportPromotions($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Name EN', 'Name AR', 'Slug', 'SEO Title EN', 'Top Offer', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [$record->id, $record->name_en, $record->name_ar, $record->slug, $record->seo_title_en, $record->top_offer ? 'Yes' : 'No', optional($record->created_at)->format('Y-m-d H:i:s')];
                if ($isDeleted) {
                    $row[] = optional($record->deleted_at)->format('Y-m-d H:i:s');
                }
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=promotions.csv']);
    }
}

