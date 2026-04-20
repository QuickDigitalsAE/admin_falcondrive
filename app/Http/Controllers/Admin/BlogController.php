<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Blog_ViewAll|Blog_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Blog_ViewAll|Blog_ViewMine|Blog_View', ['only' => ['showBlog']]);
        $this->middleware('permission:Blog_Add', ['only' => ['createBlog', 'postBlog']]);
        $this->middleware('permission:Blog_Edit', ['only' => ['editBlog', 'updateBlog']]);
        $this->middleware('permission:Blog_Delete', ['only' => ['deleteBlog']]);
        $this->middleware('permission:Blog_Revoke', ['only' => ['revokeBlog']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Blog_ViewAll')) {
            return $this->getBlogs($request);
        }

        if ($user->can('Blog_ViewMine')) {
            return $this->getMyBlogs($request);
        }

        abort(403, 'You do not have permission to view blogs.');
    }

    public function getBlogs(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $blogsQuery = $isDeleted
            ? Blog::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Blog::with(['createdByUser', 'updatedByUser']);

        $blogsQuery->orderByDesc('created_at');

        if ($search !== '') {
            $blogsQuery->where(function ($query) use ($search) {
                $query->where('title_en', 'LIKE', "%{$search}%")
                    ->orWhere('title_ar', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_en', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_ar', 'LIKE', "%{$search}%");
            });
        }

        if ($isExport) {
            return $this->exportBlogs($blogsQuery, $isDeleted);
        }

        $blogs = $blogsQuery->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $blogs->getCollection()->map(function ($blog) {
                $authUser = Auth::user();

                return [
                    'id' => $blog->id,
                    'title_en' => $blog->title_en,
                    'title_ar' => $blog->title_ar,
                    'slug' => $blog->slug,
                    'image_url' => $blog->image_url,
                    'blog_schedule' => optional($blog->blog_schedule)->format('d M Y, h:i A'),
                    'deleted_at' => $blog->deleted_at ? $blog->deleted_at->toDateTimeString() : null,
                    'created_at_human' => optional($blog->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($blog, $authUser),
                    'show_url' => route('admin.blogs.show', $blog->id),
                    'edit_url' => route('admin.blogs.edit', $blog->id),
                    'delete_url' => route('admin.blogs.delete', $blog->id),
                    'restore_url' => route('admin.blogs.revoke', $blog->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Blog_ViewAll') || $authUser->can('Blog_ViewMine') || $authUser->can('Blog_View'),
                        'can_edit' => $authUser->can('Blog_Edit'),
                        'can_delete' => $authUser->can('Blog_Delete'),
                        'can_restore' => $authUser->can('Blog_Revoke'),
                    ],
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Blogs fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $blogs->currentPage(),
                        'last_page' => $blogs->lastPage(),
                        'per_page' => $blogs->perPage(),
                        'total' => $blogs->total(),
                        'from' => $blogs->firstItem(),
                        'to' => $blogs->lastItem(),
                        'has_more_pages' => $blogs->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.blogs.index', compact('blogs'));
    }

    public function getMyBlogs(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');
        $userId = Auth::id();

        $blogsQuery = $isDeleted
            ? Blog::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Blog::with(['createdByUser', 'updatedByUser']);

        $blogsQuery->where('created_by', $userId)->orderByDesc('created_at');

        if ($search !== '') {
            $blogsQuery->where(function ($query) use ($search) {
                $query->where('title_en', 'LIKE', "%{$search}%")
                    ->orWhere('title_ar', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_en', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_ar', 'LIKE', "%{$search}%");
            });
        }

        if ($isExport) {
            return $this->exportBlogs($blogsQuery, $isDeleted);
        }

        $blogs = $blogsQuery->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $blogs->getCollection()->map(function ($blog) {
                $authUser = Auth::user();

                return [
                    'id' => $blog->id,
                    'title_en' => $blog->title_en,
                    'title_ar' => $blog->title_ar,
                    'slug' => $blog->slug,
                    'image_url' => $blog->image_url,
                    'blog_schedule' => optional($blog->blog_schedule)->format('d M Y, h:i A'),
                    'deleted_at' => $blog->deleted_at ? $blog->deleted_at->toDateTimeString() : null,
                    'created_at_human' => optional($blog->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($blog, $authUser),
                    'show_url' => route('admin.blogs.show', $blog->id),
                    'edit_url' => route('admin.blogs.edit', $blog->id),
                    'delete_url' => route('admin.blogs.delete', $blog->id),
                    'restore_url' => route('admin.blogs.revoke', $blog->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Blog_ViewAll') || $authUser->can('Blog_ViewMine') || $authUser->can('Blog_View'),
                        'can_edit' => $authUser->can('Blog_Edit'),
                        'can_delete' => $authUser->can('Blog_Delete'),
                        'can_restore' => $authUser->can('Blog_Revoke'),
                    ],
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'My blogs fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $blogs->currentPage(),
                        'last_page' => $blogs->lastPage(),
                        'per_page' => $blogs->perPage(),
                        'total' => $blogs->total(),
                        'from' => $blogs->firstItem(),
                        'to' => $blogs->lastItem(),
                        'has_more_pages' => $blogs->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.blogs.index', compact('blogs'));
    }

    public function createBlog()
    {
        return view('admin.blogs.create');
    }

    public function postBlog(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        Blog::create([
            'title_en' => $validated['title_en'],
            'title_ar' => $validated['title_ar'],
            'blog_description_en' => $validated['blog_description_en'] ?? null,
            'blog_description_ar' => $validated['blog_description_ar'] ?? null,
            'slug' => $this->generateUniqueSlug($validated['slug'] ?? $validated['title_en']),
            'seo_title_en' => $validated['seo_title_en'] ?? null,
            'seo_title_ar' => $validated['seo_title_ar'] ?? null,
            'seo_brief_en' => $validated['seo_brief_en'] ?? null,
            'seo_brief_ar' => $validated['seo_brief_ar'] ?? null,
            'image' => $this->storeImage($request),
            'blog_schedule' => $validated['blog_schedule'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.blogs')->with('success', 'Blog added successfully.');
    }

    public function showBlog($id)
    {
        $blog = Blog::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$blog) {
            return redirect()->route('admin.blogs')->with('error', 'Blog not found.');
        }

        return view('admin.blogs.show', compact('blog'));
    }

    public function editBlog($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return back()->with('error', 'Blog not found.');
        }

        return view('admin.blogs.edit', compact('blog'));
    }

    public function updateBlog(Request $request, $id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return back()->with('error', 'Blog not found.');
        }

        $validated = $request->validate($this->validationRules($id));

        if ($request->hasFile('image')) {
            $this->deleteImage($blog->image);
            $blog->image = $this->storeImage($request);
        }

        $blog->title_en = $validated['title_en'];
        $blog->title_ar = $validated['title_ar'];
        $blog->blog_description_en = $validated['blog_description_en'] ?? null;
        $blog->blog_description_ar = $validated['blog_description_ar'] ?? null;
        $blog->slug = $this->generateUniqueSlug($validated['slug'] ?? $validated['title_en'], $blog->id);
        $blog->seo_title_en = $validated['seo_title_en'] ?? null;
        $blog->seo_title_ar = $validated['seo_title_ar'] ?? null;
        $blog->seo_brief_en = $validated['seo_brief_en'] ?? null;
        $blog->seo_brief_ar = $validated['seo_brief_ar'] ?? null;
        $blog->blog_schedule = $validated['blog_schedule'] ?? null;
        $blog->updated_by = Auth::id();
        $blog->save();

        return redirect()->route('admin.blogs')->with('success', 'Blog updated successfully.');
    }

    public function deleteBlog($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return back()->with('error', 'Blog not found.');
        }

        $blog->deleted_by = Auth::id();
        $blog->save();
        $blog->delete();

        return redirect()->route('admin.blogs')->with('success', 'Blog deleted successfully.');
    }

    public function revokeBlog($id)
    {
        $blog = Blog::withTrashed()->find($id);

        if (!$blog) {
            return back()->with('error', 'Blog not found.');
        }

        if (is_null($blog->deleted_at)) {
            return back()->with('error', 'Blog is not deleted.');
        }

        $blog->restore();
        $blog->deleted_by = null;
        $blog->save();

        return redirect()->route('admin.blogs')->with('success', 'Blog restored successfully.');
    }

    private function validationRules(?int $blogId = null): array
    {
        return [
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'blog_description_en' => ['nullable', 'string'],
            'blog_description_ar' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blogId)],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_brief_en' => ['nullable', 'string'],
            'seo_brief_ar' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'blog_schedule' => ['nullable', 'date'],
        ];
    }

    private function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source);
        $baseSlug = $baseSlug ?: 'blog';
        $slug = $baseSlug;
        $counter = 1;

        while (Blog::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
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
        $folder = 'blogs/' . now()->format('FY');
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function exportBlogs($blogsQuery, bool $isDeleted = false)
    {
        $blogs = $blogsQuery->get();

        $callback = function () use ($blogs, $isDeleted) {
            $file = fopen('php://output', 'w');

            $headers = ['ID', 'Title EN', 'Title AR', 'Slug', 'SEO Title EN', 'SEO Title AR', 'Blog Schedule', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($isDeleted) {
                $headers[] = 'Deleted By';
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($blogs as $blog) {
                $row = [
                    $blog->id,
                    $blog->title_en,
                    $blog->title_ar,
                    $blog->slug,
                    $blog->seo_title_en,
                    $blog->seo_title_ar,
                    optional($blog->blog_schedule)->format('Y-m-d H:i:s'),
                    optional($blog->createdByUser)->name,
                    optional($blog->updatedByUser)->name,
                    optional($blog->created_at)->format('Y-m-d H:i:s'),
                    optional($blog->updated_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($blog->deletedByUser)->name;
                    $row[] = optional($blog->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=blogs.csv',
        ]);
    }
}

