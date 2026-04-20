<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Faq_ViewAll|Faq_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Faq_ViewAll|Faq_ViewMine|Faq_View', ['only' => ['show']]);
        $this->middleware('permission:Faq_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Faq_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Faq_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Faq_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Faq_ViewAll')) {
            return $this->getFaqs($request);
        }

        if ($user->can('Faq_ViewMine')) {
            return $this->getMyFaqs($request);
        }

        abort(403, 'You do not have permission to view FAQs.');
    }

    public function getFaqs(Request $request)
    {
        return $this->listFaqs($request);
    }

    public function getMyFaqs(Request $request)
    {
        return $this->listFaqs($request, true);
    }

    public function create()
    {
        return view('admin.faq.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateFaq($request);
        $validated['created_by'] = Auth::id();

        Faq::create($validated);

        return redirect()->route('admin.faq')->with('success', 'FAQ created successfully.');
    }

    public function show(int $id)
    {
        $faq = Faq::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$faq) {
            return redirect()->route('admin.faq')->with('error', 'FAQ not found.');
        }

        return view('admin.faq.show', compact('faq'));
    }

    public function edit(int $id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return redirect()->route('admin.faq')->with('error', 'FAQ not found.');
        }

        return view('admin.faq.edit', compact('faq'));
    }

    public function update(Request $request, int $id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return redirect()->route('admin.faq')->with('error', 'FAQ not found.');
        }

        $validated = $this->validateFaq($request);
        $validated['updated_by'] = Auth::id();

        $faq->update($validated);

        return redirect()->route('admin.faq')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(int $id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return back()->with('error', 'FAQ not found.');
        }

        $faq->deleted_by = Auth::id();
        $faq->save();
        $faq->delete();

        return redirect()->route('admin.faq')->with('success', 'FAQ deleted successfully.');
    }

    public function restore(int $id)
    {
        $faq = Faq::withTrashed()->find($id);

        if (!$faq) {
            return back()->with('error', 'FAQ not found.');
        }

        if (is_null($faq->deleted_at)) {
            return back()->with('error', 'FAQ is not deleted.');
        }

        $faq->restore();
        $faq->deleted_by = null;
        $faq->save();

        return redirect()->route('admin.faq')->with('success', 'FAQ restored successfully.');
    }

    private function listFaqs(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Faq::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Faq::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('question_en', 'LIKE', "%{$search}%")
                    ->orWhere('question_ar', 'LIKE', "%{$search}%")
                    ->orWhere('answer_en', 'LIKE', "%{$search}%")
                    ->orWhere('answer_ar', 'LIKE', "%{$search}%");
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->export($query, $isDeleted);
        }

        $faqs = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $list = $faqs->getCollection()->map(function (Faq $faq) {
                $authUser = Auth::user();

                return [
                    'id' => $faq->id,
                    'question_en' => $faq->question_en,
                    'question_ar' => $faq->question_ar,
                    'answer_preview' => Str::limit(strip_tags((string) $faq->answer_en), 120),
                    'deleted_at' => optional($faq->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($faq->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($faq, $authUser),
                    'show_url' => route('admin.faq.show', $faq->id),
                    'edit_url' => route('admin.faq.edit', $faq->id),
                    'delete_url' => route('admin.faq.delete', $faq->id),
                    'restore_url' => route('admin.faq.restore', $faq->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Faq_ViewAll') || $authUser->can('Faq_ViewMine') || $authUser->can('Faq_View'),
                        'can_edit' => $authUser->can('Faq_Edit'),
                        'can_delete' => $authUser->can('Faq_Delete'),
                        'can_restore' => $authUser->can('Faq_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My FAQs fetched successfully.' : 'FAQs fetched successfully.',
                'data' => [
                    'items' => $list,
                    'pagination' => [
                        'current_page' => $faqs->currentPage(),
                        'last_page' => $faqs->lastPage(),
                        'per_page' => $faqs->perPage(),
                        'total' => $faqs->total(),
                        'from' => $faqs->firstItem(),
                        'to' => $faqs->lastItem(),
                        'has_more_pages' => $faqs->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'is_deleted' => $isDeleted,
                    ],
                ],
            ]);
        }

        return view('admin.faq.index');
    }

    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'question_en' => ['required', 'string', 'max:255'],
            'question_ar' => ['required', 'string', 'max:255'],
            'answer_en' => ['required', 'string'],
            'answer_ar' => ['required', 'string'],
        ]);
    }

    private function export($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Question EN', 'Question AR', 'Answer EN', 'Created At'];
            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }
            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
                    $record->question_en,
                    $record->question_ar,
                    Str::limit($record->answer_en, 120),
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
            'Content-Disposition' => 'attachment; filename=faq.csv',
        ]);
    }
}

