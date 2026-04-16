<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestimonialController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Testimonial_ViewAll|Testimonial_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Testimonial_ViewAll|Testimonial_ViewMine|Testimonial_View', ['only' => ['show']]);
        $this->middleware('permission:Testimonial_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Testimonial_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Testimonial_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Testimonial_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Testimonial_ViewAll')) {
            return $this->getTestimonials($request);
        }

        if ($user->can('Testimonial_ViewMine')) {
            return $this->getMyTestimonials($request);
        }

        abort(403, 'You do not have permission to view testimonials.');
    }

    public function getTestimonials(Request $request)
    {
        return $this->listTestimonials($request);
    }

    public function getMyTestimonials(Request $request)
    {
        return $this->listTestimonials($request, true);
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTestimonial($request);
        $validated['created_by'] = Auth::id();
        $validated['image'] = $this->storeImage($request);
        Testimonial::create($validated);

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial created successfully.');
    }

    public function show(int $id)
    {
        $testimonial = Testimonial::withTrashed()->find($id);
        if (!$testimonial) {
            return redirect()->route('admin.testimonials')->with('error', 'Testimonial not found.');
        }

        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function edit(int $id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return redirect()->route('admin.testimonials')->with('error', 'Testimonial not found.');
        }

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, int $id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return redirect()->route('admin.testimonials')->with('error', 'Testimonial not found.');
        }

        $validated = $this->validateTestimonial($request);
        $validated['updated_by'] = Auth::id();

        if ($request->boolean('remove_image')) {
            $this->deleteImage($testimonial->image);
            $validated['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($testimonial->image);
            $validated['image'] = $this->storeImage($request);
        }

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(int $id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return back()->with('error', 'Testimonial not found.');
        }

        $testimonial->deleted_by = Auth::id();
        $testimonial->save();
        $testimonial->delete();

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial deleted successfully.');
    }

    public function restore(int $id)
    {
        $testimonial = Testimonial::withTrashed()->find($id);
        if (!$testimonial) {
            return back()->with('error', 'Testimonial not found.');
        }

        $testimonial->restore();
        $testimonial->deleted_by = null;
        $testimonial->save();

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial restored successfully.');
    }

    private function listTestimonials(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Testimonial::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Testimonial::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('description_en', 'LIKE', "%{$search}%")
                    ->orWhere('description_ar', 'LIKE', "%{$search}%");
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->export($query, $isDeleted);
        }

        $testimonials = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $testimonials->getCollection()->map(function (Testimonial $testimonial) {
                $authUser = Auth::user();

                return [
                    'id' => $testimonial->id,
                    'name_en' => $testimonial->name_en,
                    'name_ar' => $testimonial->name_ar,
                    'description_preview' => mb_strimwidth(strip_tags((string) $testimonial->description_en), 0, 120, '...'),
                    'image_url' => $testimonial->image_url,
                    'deleted_at' => optional($testimonial->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($testimonial->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.testimonials.show', $testimonial->id),
                    'edit_url' => route('admin.testimonials.edit', $testimonial->id),
                    'delete_url' => route('admin.testimonials.delete', $testimonial->id),
                    'restore_url' => route('admin.testimonials.restore', $testimonial->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Testimonial_ViewAll') || $authUser->can('Testimonial_ViewMine') || $authUser->can('Testimonial_View'),
                        'can_edit' => $authUser->can('Testimonial_Edit'),
                        'can_delete' => $authUser->can('Testimonial_Delete'),
                        'can_restore' => $authUser->can('Testimonial_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My testimonials fetched successfully.' : 'Testimonials fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $testimonials->currentPage(),
                        'last_page' => $testimonials->lastPage(),
                        'per_page' => $testimonials->perPage(),
                        'total' => $testimonials->total(),
                        'from' => $testimonials->firstItem(),
                        'to' => $testimonials->lastItem(),
                        'has_more_pages' => $testimonials->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.testimonials.index');
    }

    private function validateTestimonial(Request $request): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_ar' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'in:0,1'],
        ]);
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

    private function export($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Name EN', 'Name AR', 'Description EN', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
                    $record->name_en,
                    $record->name_ar,
                    mb_strimwidth(strip_tags((string) $record->description_en), 0, 120, '...'),
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
            'Content-Disposition' => 'attachment; filename=testimonials.csv',
        ]);
    }
}
