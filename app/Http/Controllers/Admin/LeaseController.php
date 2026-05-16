<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LeaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Lease_ViewAll|Lease_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Lease_ViewAll|Lease_ViewMine|Lease_View', ['only' => ['show']]);
        $this->middleware('permission:Lease_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Lease_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Lease_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Lease_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Lease_ViewAll')) {
            return $this->getLeases($request);
        }

        if ($user->can('Lease_ViewMine')) {
            return $this->getMyLeases($request);
        }

        abort(403, 'You do not have permission to view lease records.');
    }

    public function getLeases(Request $request)
    {
        return $this->listLeases($request);
    }

    public function getMyLeases(Request $request)
    {
        return $this->listLeases($request, true);
    }

    public function create()
    {
        return view('admin.lease.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateLease($request);
        $validated['slug'] = $this->normalizeSlug($validated['slug']);
        $validated['created_by'] = Auth::id();
        Lease::create($validated);

        return redirect()->route('admin.lease')->with('success', 'Lease record created successfully.');
    }

    public function show(int $id)
    {
        $lease = Lease::withTrashed()
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->find($id);
        if (!$lease) {
            return redirect()->route('admin.lease')->with('error', 'Lease record not found.');
        }

        return view('admin.lease.show', compact('lease'));
    }

    public function edit(int $id)
    {
        $lease = Lease::find($id);
        if (!$lease) {
            return redirect()->route('admin.lease')->with('error', 'Lease record not found.');
        }

        return view('admin.lease.edit', compact('lease'));
    }

    public function update(Request $request, int $id)
    {
        $lease = Lease::find($id);
        if (!$lease) {
            return redirect()->route('admin.lease')->with('error', 'Lease record not found.');
        }

        $validated = $this->validateLease($request, $lease->id);
        $validated['slug'] = $this->normalizeSlug($validated['slug']);
        $validated['updated_by'] = Auth::id();
        $lease->update($validated);

        return redirect()->route('admin.lease')->with('success', 'Lease record updated successfully.');
    }

    public function destroy(int $id)
    {
        $lease = Lease::find($id);
        if (!$lease) {
            return back()->with('error', 'Lease record not found.');
        }

        $lease->deleted_by = Auth::id();
        $lease->save();
        $lease->delete();

        return redirect()->route('admin.lease')->with('success', 'Lease record deleted successfully.');
    }

    public function restore(int $id)
    {
        $lease = Lease::withTrashed()->find($id);
        if (!$lease) {
            return back()->with('error', 'Lease record not found.');
        }

        $lease->restore();
        $lease->deleted_by = null;
        $lease->save();

        return redirect()->route('admin.lease')->with('success', 'Lease record restored successfully.');
    }

    private function listLeases(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Lease::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Lease::with(['createdByUser', 'updatedByUser']);

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

        $leases = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $leases->getCollection()->map(function (Lease $lease) {
                $authUser = Auth::user();

                return [
                    'id' => $lease->id,
                    'name_en' => $lease->name_en,
                    'name_ar' => $lease->name_ar,
                    'slug' => $lease->slug,
                    'seo_title_en' => $lease->seo_title_en,
                    'deleted_at' => optional($lease->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($lease->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($lease, $authUser),
                    'show_url' => route('admin.lease.show', $lease->id),
                    'edit_url' => route('admin.lease.edit', $lease->id),
                    'delete_url' => route('admin.lease.delete', $lease->id),
                    'restore_url' => route('admin.lease.restore', $lease->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Lease_ViewAll') || $authUser->can('Lease_ViewMine') || $authUser->can('Lease_View'),
                        'can_edit' => $authUser->can('Lease_Edit'),
                        'can_delete' => $authUser->can('Lease_Delete'),
                        'can_restore' => $authUser->can('Lease_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My lease records fetched successfully.' : 'Lease records fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $leases->currentPage(),
                        'last_page' => $leases->lastPage(),
                        'per_page' => $leases->perPage(),
                        'total' => $leases->total(),
                        'from' => $leases->firstItem(),
                        'to' => $leases->lastItem(),
                        'has_more_pages' => $leases->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.lease.index');
    }

    private function validateLease(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'seo_title_en' => ['required', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_brief_en' => ['required', 'string'],
            'seo_brief_ar' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('lease', 'slug')->ignore($id)],
        ]);
    }

    private function normalizeSlug(string $source): string
    {
        $source = strtolower(trim($source));
        $source = preg_replace('/[^a-z0-9-]+/', '-', $source) ?? '';
        $source = preg_replace('/-+/', '-', $source) ?? '';

        return trim($source, '-');
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
            'Content-Disposition' => 'attachment; filename=lease.csv',
        ]);
    }
}

