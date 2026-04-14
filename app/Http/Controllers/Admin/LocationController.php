<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Location_ViewAll|Location_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Location_ViewAll|Location_ViewMine|Location_View', ['only' => ['show']]);
        $this->middleware('permission:Location_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Location_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Location_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Location_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Location_ViewAll')) {
            return $this->getLocations($request);
        }

        if ($user->can('Location_ViewMine')) {
            return $this->getMyLocations($request);
        }

        abort(403, 'You do not have permission to view locations.');
    }

    public function getLocations(Request $request)
    {
        return $this->listLocations($request);
    }

    public function getMyLocations(Request $request)
    {
        return $this->listLocations($request, true);
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateLocation($request);
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['created_by'] = Auth::id();
        Location::create($validated);

        return redirect()->route('admin.locations')->with('success', 'Location created successfully.');
    }

    public function show(int $id)
    {
        $location = Location::withTrashed()->find($id);
        if (!$location) {
            return redirect()->route('admin.locations')->with('error', 'Location not found.');
        }

        return view('admin.locations.show', compact('location'));
    }

    public function edit(int $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return redirect()->route('admin.locations')->with('error', 'Location not found.');
        }

        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, int $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return redirect()->route('admin.locations')->with('error', 'Location not found.');
        }

        $validated = $this->validateLocation($request, $location->id);
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['updated_by'] = Auth::id();
        $location->update($validated);

        return redirect()->route('admin.locations')->with('success', 'Location updated successfully.');
    }

    public function destroy(int $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return back()->with('error', 'Location not found.');
        }

        $location->deleted_by = Auth::id();
        $location->save();
        $location->delete();

        return redirect()->route('admin.locations')->with('success', 'Location deleted successfully.');
    }

    public function restore(int $id)
    {
        $location = Location::withTrashed()->find($id);
        if (!$location) {
            return back()->with('error', 'Location not found.');
        }

        $location->restore();
        $location->deleted_by = null;
        $location->save();

        return redirect()->route('admin.locations')->with('success', 'Location restored successfully.');
    }

    private function listLocations(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Location::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Location::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_en', 'LIKE', "%{$search}%");
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->export($query, $isDeleted);
        }

        $locations = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $locations->getCollection()->map(function (Location $location) {
                $authUser = Auth::user();

                return [
                    'id' => $location->id,
                    'name_en' => $location->name_en,
                    'name_ar' => $location->name_ar,
                    'slug' => $location->slug,
                    'seo_title_en' => $location->seo_title_en,
                    'deleted_at' => optional($location->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($location->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.locations.show', $location->id),
                    'edit_url' => route('admin.locations.edit', $location->id),
                    'delete_url' => route('admin.locations.delete', $location->id),
                    'restore_url' => route('admin.locations.restore', $location->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Location_ViewAll') || $authUser->can('Location_ViewMine') || $authUser->can('Location_View'),
                        'can_edit' => $authUser->can('Location_Edit'),
                        'can_delete' => $authUser->can('Location_Delete'),
                        'can_restore' => $authUser->can('Location_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My locations fetched successfully.' : 'Locations fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $locations->currentPage(),
                        'last_page' => $locations->lastPage(),
                        'per_page' => $locations->perPage(),
                        'total' => $locations->total(),
                        'from' => $locations->firstItem(),
                        'to' => $locations->lastItem(),
                        'has_more_pages' => $locations->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.locations.index');
    }

    private function validateLocation(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_ar' => ['required', 'string'],
            'seo_title_en' => ['required', 'string', 'max:255'],
            'seo_title_ar' => ['required', 'string', 'max:255'],
            'seo_brief_en' => ['required', 'string'],
            'seo_brief_ar' => ['required', 'string'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('locations', 'slug')->ignore($id)],
        ]);
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
            'Content-Disposition' => 'attachment; filename=locations.csv',
        ]);
    }
}
