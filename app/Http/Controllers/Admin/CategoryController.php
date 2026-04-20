<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Category_ViewAll|Category_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Category_ViewAll|Category_ViewMine|Category_View', ['only' => ['show']]);
        $this->middleware('permission:Category_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Category_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Category_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Category_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Category_ViewAll')) {
            return $this->getCategories($request);
        }

        if ($user->can('Category_ViewMine')) {
            return $this->getMyCategories($request);
        }

        abort(403, 'You do not have permission to view categories.');
    }

    public function getCategories(Request $request)
    {
        return $this->listCategories($request);
    }

    public function getMyCategories(Request $request)
    {
        return $this->listCategories($request, true);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['image'] = $this->storeImage($request);
        $validated['created_by'] = Auth::id();

        Category::create($validated);

        return redirect()->route('admin.categories')->with('success', 'Category created successfully.');
    }

    public function show(int $id)
    {
        $category = Category::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$category) {
            return redirect()->route('admin.categories')->with('error', 'Category not found.');
        }

        return view('admin.categories.show', compact('category'));
    }

    public function edit(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return redirect()->route('admin.categories')->with('error', 'Category not found.');
        }

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return redirect()->route('admin.categories')->with('error', 'Category not found.');
        }

        $validated = $this->validateCategory($request, $category->id);
        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $validated['image'] = $this->storeImage($request);
        }

        $validated['updated_by'] = Auth::id();

        $category->update($validated);

        return redirect()->route('admin.categories')->with('success', 'Category updated successfully.');
    }

    public function destroy(int $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return back()->with('error', 'Category not found.');
        }

        $category->deleted_by = Auth::id();
        $category->save();
        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully.');
    }

    public function restore(int $id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return back()->with('error', 'Category not found.');
        }

        if (is_null($category->deleted_at)) {
            return back()->with('error', 'Category is not deleted.');
        }

        $category->restore();
        $category->deleted_by = null;
        $category->save();

        return redirect()->route('admin.categories')->with('success', 'Category restored successfully.');
    }

    private function listCategories(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Category::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Category::with(['createdByUser', 'updatedByUser']);

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
            return $this->export($query, $isDeleted);
        }

        $categories = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $categories->getCollection()->map(function (Category $category) {
                $authUser = Auth::user();

                return [
                    'id' => $category->id,
                    'name_en' => $category->name_en,
                    'name_ar' => $category->name_ar,
                    'slug' => $category->slug,
                    'seo_title_en' => $category->seo_title_en,
                    'image' => $category->image,
                    'image_url' => $category->image_url,
                    'deleted_at' => optional($category->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($category->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($category, $authUser),
                    'show_url' => route('admin.categories.show', $category->id),
                    'edit_url' => route('admin.categories.edit', $category->id),
                    'delete_url' => route('admin.categories.delete', $category->id),
                    'restore_url' => route('admin.categories.restore', $category->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Category_ViewAll') || $authUser->can('Category_ViewMine') || $authUser->can('Category_View'),
                        'can_edit' => $authUser->can('Category_Edit'),
                        'can_delete' => $authUser->can('Category_Delete'),
                        'can_restore' => $authUser->can('Category_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My categories fetched successfully.' : 'Categories fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $categories->currentPage(),
                        'last_page' => $categories->lastPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'from' => $categories->firstItem(),
                        'to' => $categories->lastItem(),
                        'has_more_pages' => $categories->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.categories.index');
    }

    private function validateCategory(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_brief_en' => ['nullable', 'string'],
            'seo_brief_ar' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($id)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $folder = 'categories/' . now()->format('FY');
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function deleteImage(?string $path): void
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
            $headers = ['ID', 'Name EN', 'Name AR', 'Slug', 'SEO Title EN', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
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
            'Content-Disposition' => 'attachment; filename=categories.csv',
        ]);
    }
}

