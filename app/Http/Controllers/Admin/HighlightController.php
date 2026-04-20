<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Highlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HighlightController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Highlight_ViewAll|Highlight_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Highlight_ViewAll|Highlight_ViewMine|Highlight_View', ['only' => ['show']]);
        $this->middleware('permission:Highlight_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Highlight_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Highlight_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Highlight_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Highlight_ViewAll')) {
            return $this->listHighlights($request);
        }

        if ($user->can('Highlight_ViewMine')) {
            return $this->listHighlights($request, true);
        }

        abort(403, 'You do not have permission to view highlights.');
    }

    public function create()
    {
        return view('admin.highlights.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        Highlight::create([
            'title_en' => $validated['title_en'],
            'title_ar' => $validated['title_ar'],
            'image' => $this->storeImage($request),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.highlights')->with('success', 'Highlight added successfully.');
    }

    public function show(int $id)
    {
        $highlight = Highlight::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$highlight) {
            return redirect()->route('admin.highlights')->with('error', 'Highlight not found.');
        }

        return view('admin.highlights.show', compact('highlight'));
    }

    public function edit(int $id)
    {
        $highlight = Highlight::find($id);

        if (!$highlight) {
            return back()->with('error', 'Highlight not found.');
        }

        return view('admin.highlights.edit', compact('highlight'));
    }

    public function update(Request $request, int $id)
    {
        $highlight = Highlight::find($id);

        if (!$highlight) {
            return back()->with('error', 'Highlight not found.');
        }

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImage($highlight->image);
            $highlight->image = $this->storeImage($request);
        }

        $highlight->title_en = $validated['title_en'];
        $highlight->title_ar = $validated['title_ar'];
        $highlight->updated_by = Auth::id();
        $highlight->save();

        return redirect()->route('admin.highlights')->with('success', 'Highlight updated successfully.');
    }

    public function destroy(int $id)
    {
        $highlight = Highlight::find($id);

        if (!$highlight) {
            return back()->with('error', 'Highlight not found.');
        }

        $highlight->deleted_by = Auth::id();
        $highlight->save();
        $highlight->delete();

        return redirect()->route('admin.highlights')->with('success', 'Highlight deleted successfully.');
    }

    public function restore(int $id)
    {
        $highlight = Highlight::withTrashed()->find($id);

        if (!$highlight) {
            return back()->with('error', 'Highlight not found.');
        }

        if (is_null($highlight->deleted_at)) {
            return back()->with('error', 'Highlight is not deleted.');
        }

        $highlight->restore();
        $highlight->deleted_by = null;
        $highlight->save();

        return redirect()->route('admin.highlights')->with('success', 'Highlight restored successfully.');
    }

    private function listHighlights(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Highlight::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Highlight::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(fn ($subQuery) => $subQuery->where('title_en', 'LIKE', "%{$search}%")->orWhere('title_ar', 'LIKE', "%{$search}%"));
        }

        $query->latest('id');

        if ($isExport) {
            return $this->exportHighlights($query, $isDeleted);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $records->getCollection()->map(function (Highlight $highlight) use ($authUser) {
                return [
                    'id' => $highlight->id,
                    'title_en' => $highlight->title_en,
                    'title_ar' => $highlight->title_ar,
                    'image_url' => $highlight->image_url,
                    'deleted_at' => optional($highlight->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($highlight->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($highlight, $authUser),
                    'show_url' => route('admin.highlights.show', $highlight->id),
                    'edit_url' => route('admin.highlights.edit', $highlight->id),
                    'delete_url' => route('admin.highlights.delete', $highlight->id),
                    'restore_url' => route('admin.highlights.restore', $highlight->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Highlight_ViewAll') || $authUser->can('Highlight_ViewMine') || $authUser->can('Highlight_View'),
                        'can_edit' => $authUser->can('Highlight_Edit'),
                        'can_delete' => $authUser->can('Highlight_Delete'),
                        'can_restore' => $authUser->can('Highlight_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My highlights fetched successfully.' : 'Highlights fetched successfully.',
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

        return view('admin.highlights.index');
    }

    private function storeImage(Request $request): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $folder = 'highlights/' . now()->format('FY');
        $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $fileName, 'public');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function exportHighlights($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Title EN', 'Title AR', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [$record->id, $record->title_en, $record->title_ar, optional($record->created_at)->format('Y-m-d H:i:s')];
                if ($isDeleted) {
                    $row[] = optional($record->deleted_at)->format('Y-m-d H:i:s');
                }
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=highlights.csv']);
    }
}

