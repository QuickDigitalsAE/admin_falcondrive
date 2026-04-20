<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Inquiry_ViewAll|Inquiry_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Inquiry_ViewAll|Inquiry_ViewMine|Inquiry_View', ['only' => ['show']]);
        $this->middleware('permission:Inquiry_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Inquiry_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Inquiry_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Inquiry_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Inquiry_ViewAll')) {
            return $this->listInquiries($request);
        }

        if ($user->can('Inquiry_ViewMine')) {
            return $this->listInquiries($request, true);
        }

        abort(403, 'You do not have permission to view inquiries.');
    }

    public function create()
    {
        return view('admin.inquiries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string'],
            'promo_code' => ['nullable', 'string', 'max:255'],
            'car_name' => ['nullable', 'string', 'max:255'],
        ]);

        Inquiry::create($validated + ['created_by' => Auth::id()]);

        return redirect()->route('admin.inquiries')->with('success', 'Inquiry added successfully.');
    }

    public function show(int $id)
    {
        $inquiry = Inquiry::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);
        if (!$inquiry) {
            return redirect()->route('admin.inquiries')->with('error', 'Inquiry not found.');
        }
        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function edit(int $id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) {
            return back()->with('error', 'Inquiry not found.');
        }
        return view('admin.inquiries.edit', compact('inquiry'));
    }

    public function update(Request $request, int $id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) {
            return back()->with('error', 'Inquiry not found.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string'],
            'promo_code' => ['nullable', 'string', 'max:255'],
            'car_name' => ['nullable', 'string', 'max:255'],
        ]);

        $inquiry->update($validated + ['updated_by' => Auth::id()]);

        return redirect()->route('admin.inquiries')->with('success', 'Inquiry updated successfully.');
    }

    public function destroy(int $id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) {
            return back()->with('error', 'Inquiry not found.');
        }
        $inquiry->deleted_by = Auth::id();
        $inquiry->save();
        $inquiry->delete();
        return redirect()->route('admin.inquiries')->with('success', 'Inquiry deleted successfully.');
    }

    public function restore(int $id)
    {
        $inquiry = Inquiry::withTrashed()->find($id);
        if (!$inquiry) {
            return back()->with('error', 'Inquiry not found.');
        }
        if (is_null($inquiry->deleted_at)) {
            return back()->with('error', 'Inquiry is not deleted.');
        }
        $inquiry->restore();
        $inquiry->deleted_by = null;
        $inquiry->save();
        return redirect()->route('admin.inquiries')->with('success', 'Inquiry restored successfully.');
    }

    private function listInquiries(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted ? Inquiry::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser']) : Inquiry::with(['createdByUser', 'updatedByUser']);
        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('number', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('promo_code', 'LIKE', "%{$search}%")
                    ->orWhere('car_name', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }
        $query->latest('id');

        if ($isExport) {
            return $this->exportInquiries($query, $isDeleted);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $records->getCollection()->map(function (Inquiry $inquiry) use ($authUser) {
                return [
                    'id' => $inquiry->id,
                    'name' => $inquiry->name,
                    'number' => $inquiry->number,
                    'email' => $inquiry->email,
                    'promo_code' => $inquiry->promo_code,
                    'car_name' => $inquiry->car_name,
                    'message' => $inquiry->message,
                    'deleted_at' => optional($inquiry->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($inquiry->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($inquiry, $authUser),
                    'show_url' => route('admin.inquiries.show', $inquiry->id),
                    'edit_url' => route('admin.inquiries.edit', $inquiry->id),
                    'delete_url' => route('admin.inquiries.delete', $inquiry->id),
                    'restore_url' => route('admin.inquiries.restore', $inquiry->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Inquiry_ViewAll') || $authUser->can('Inquiry_ViewMine') || $authUser->can('Inquiry_View'),
                        'can_edit' => $authUser->can('Inquiry_Edit'),
                        'can_delete' => $authUser->can('Inquiry_Delete'),
                        'can_restore' => $authUser->can('Inquiry_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My inquiries fetched successfully.' : 'Inquiries fetched successfully.',
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

        return view('admin.inquiries.index');
    }

    private function exportInquiries($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Name', 'Number', 'Email', 'Promo Code', 'Car Name', 'Message', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [$record->id, $record->name, $record->number, $record->email, $record->promo_code, $record->car_name, preg_replace('/\s+/', ' ', (string) $record->message), optional($record->created_at)->format('Y-m-d H:i:s')];
                if ($isDeleted) {
                    $row[] = optional($record->deleted_at)->format('Y-m-d H:i:s');
                }
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=inquiries.csv']);
    }
}

