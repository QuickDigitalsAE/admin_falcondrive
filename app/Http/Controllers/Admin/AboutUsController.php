<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AboutUsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:AboutUs_ViewAll|AboutUs_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:AboutUs_ViewAll|AboutUs_ViewMine|AboutUs_View', ['only' => ['show']]);
        $this->middleware('permission:AboutUs_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:AboutUs_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:AboutUs_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:AboutUs_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('AboutUs_ViewAll')) {
            return $this->getAboutUs($request);
        }

        if ($user->can('AboutUs_ViewMine')) {
            return $this->getMyAboutUs($request);
        }

        abort(403, 'You do not have permission to view About Us records.');
    }

    public function getAboutUs(Request $request)
    {
        return $this->listAboutUs($request);
    }

    public function getMyAboutUs(Request $request)
    {
        return $this->listAboutUs($request, true);
    }

    public function create()
    {
        return view('admin.about-us.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateAboutUs($request);

        AboutUs::create($validated + [
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.about-us')->with('success', 'About Us record created successfully.');
    }

    public function show(int $id)
    {
        $aboutUs = AboutUs::withTrashed()
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->find($id);

        if (!$aboutUs) {
            return redirect()->route('admin.about-us')->with('error', 'About Us record not found.');
        }

        return view('admin.about-us.show', compact('aboutUs'));
    }

    public function edit(int $id)
    {
        $aboutUs = AboutUs::find($id);

        if (!$aboutUs) {
            return redirect()->route('admin.about-us')->with('error', 'About Us record not found.');
        }

        return view('admin.about-us.edit', compact('aboutUs'));
    }

    public function update(Request $request, int $id)
    {
        $aboutUs = AboutUs::find($id);

        if (!$aboutUs) {
            return redirect()->route('admin.about-us')->with('error', 'About Us record not found.');
        }

        $validated = $this->validateAboutUs($request);

        $aboutUs->update($validated + [
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.about-us')->with('success', 'About Us record updated successfully.');
    }

    public function destroy(int $id)
    {
        $aboutUs = AboutUs::find($id);

        if (!$aboutUs) {
            return back()->with('error', 'About Us record not found.');
        }

        $aboutUs->deleted_by = Auth::id();
        $aboutUs->save();
        $aboutUs->delete();

        return redirect()->route('admin.about-us')->with('success', 'About Us record deleted successfully.');
    }

    public function restore(int $id)
    {
        $aboutUs = AboutUs::withTrashed()->find($id);

        if (!$aboutUs) {
            return back()->with('error', 'About Us record not found.');
        }

        if (is_null($aboutUs->deleted_at)) {
            return back()->with('error', 'About Us record is not deleted.');
        }

        $aboutUs->restore();
        $aboutUs->deleted_by = null;
        $aboutUs->save();

        return redirect()->route('admin.about-us')->with('success', 'About Us record restored successfully.');
    }

    private function listAboutUs(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $aboutUsQuery = $isDeleted
            ? AboutUs::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : AboutUs::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $aboutUsQuery->where('created_by', Auth::id());
        }

        $aboutUsQuery->orderByDesc('created_at');

        if ($search !== '') {
            $aboutUsQuery->where(function ($query) use ($search) {
                $query->where('seo_title_en', 'LIKE', "%{$search}%")
                    ->orWhere('seo_title_ar', 'LIKE', "%{$search}%")
                    ->orWhere('first_section_en', 'LIKE', "%{$search}%")
                    ->orWhere('first_section_ar', 'LIKE', "%{$search}%")
                    ->orWhere('mission_en', 'LIKE', "%{$search}%")
                    ->orWhere('mission_ar', 'LIKE', "%{$search}%")
                    ->orWhere('vision_en', 'LIKE', "%{$search}%")
                    ->orWhere('vision_ar', 'LIKE', "%{$search}%");
            });
        }

        if ($isExport) {
            return $this->exportAboutUs($aboutUsQuery, $isDeleted);
        }

        $aboutUsRecords = $aboutUsQuery->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My About Us records fetched successfully.' : 'About Us records fetched successfully.',
                'data' => [
                    'items' => $aboutUsRecords->getCollection()->map(function (AboutUs $aboutUs) use ($authUser) {
                        return [
                            'id' => $aboutUs->id,
                            'seo_title_en' => $aboutUs->seo_title_en,
                            'seo_title_ar' => $aboutUs->seo_title_ar,
                            'first_section_preview' => Str::limit(strip_tags((string) $aboutUs->first_section_en), 90),
                            'mission_preview' => Str::limit(strip_tags((string) $aboutUs->mission_en), 70),
                            'deleted_at' => optional($aboutUs->deleted_at)->toDateTimeString(),
                            'created_at_human' => optional($aboutUs->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($aboutUs, $authUser),
                            'show_url' => route('admin.about-us.show', $aboutUs->id),
                            'edit_url' => route('admin.about-us.edit', $aboutUs->id),
                            'delete_url' => route('admin.about-us.delete', $aboutUs->id),
                            'restore_url' => route('admin.about-us.restore', $aboutUs->id),
                            'permissions' => [
                                'can_view' => $authUser->can('AboutUs_ViewAll') || $authUser->can('AboutUs_ViewMine') || $authUser->can('AboutUs_View'),
                                'can_edit' => $authUser->can('AboutUs_Edit'),
                                'can_delete' => $authUser->can('AboutUs_Delete'),
                                'can_restore' => $authUser->can('AboutUs_Revoke'),
                            ],
                        ];
                    })->values(),
                    'pagination' => [
                        'current_page' => $aboutUsRecords->currentPage(),
                        'last_page' => $aboutUsRecords->lastPage(),
                        'per_page' => $aboutUsRecords->perPage(),
                        'total' => $aboutUsRecords->total(),
                        'from' => $aboutUsRecords->firstItem(),
                        'to' => $aboutUsRecords->lastItem(),
                        'has_more_pages' => $aboutUsRecords->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.about-us.index');
    }

    private function validateAboutUs(Request $request): array
    {
        return $request->validate([
            'first_section_en' => ['required', 'string'],
            'first_section_ar' => ['required', 'string'],
            'mission_en' => ['required', 'string'],
            'mission_ar' => ['required', 'string'],
            'vision_en' => ['required', 'string'],
            'vision_ar' => ['required', 'string'],
            'seo_title_en' => ['required', 'string', 'max:255'],
            'seo_title_ar' => ['required', 'string', 'max:255'],
            'seo_brief_en' => ['required', 'string'],
            'seo_brief_ar' => ['required', 'string'],
        ]);
    }

    private function exportAboutUs($aboutUsQuery, bool $isDeleted)
    {
        $records = $aboutUsQuery->get();

        $callback = function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');

            $headers = [
                'ID',
                'SEO Title EN',
                'SEO Title AR',
                'First Section EN',
                'Mission EN',
                'Vision EN',
                'Created At',
                'Updated At',
            ];

            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
                    $record->seo_title_en,
                    $record->seo_title_ar,
                    Str::limit(preg_replace('/\s+/', ' ', strip_tags($record->first_section_en)), 120),
                    Str::limit(preg_replace('/\s+/', ' ', strip_tags($record->mission_en)), 120),
                    Str::limit(preg_replace('/\s+/', ' ', strip_tags($record->vision_en)), 120),
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                    optional($record->updated_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($record->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=about-us.csv',
        ]);
    }
}

