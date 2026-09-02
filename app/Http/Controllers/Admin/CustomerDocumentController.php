<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:CustomerDocument_ViewAll|CustomerDocument_View', ['only' => ['index']]);
        $this->middleware('permission:CustomerDocument_ViewAll|CustomerDocument_View', ['only' => ['show']]);
        $this->middleware('permission:CustomerDocument_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:CustomerDocument_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:CustomerDocument_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:CustomerDocument_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $query = ($isDeleted ? CustomerDocument::onlyTrashed() : CustomerDocument::query())
            ->with(['createdByUser', 'customer'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('document_no', 'like', "%{$search}%")
                    ->orWhere('identity_name', 'like', "%{$search}%")
                    ->orWhere('issued_by', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $documents = $query->paginate((int) $request->query('per_page', 10))->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $documents->getCollection()->map(function (CustomerDocument $document) use ($authUser) {
                return [
                    'id' => $document->id,
                    'customer_id' => $document->customer_id,
                    'customer_name' => trim(($document->customer?->first_name ?? '') . ' ' . ($document->customer?->last_name ?? '')),
                    'customer_email' => $document->customer?->email,
                    'identity_name' => $document->identity_name,
                    'identity_document_id' => $document->identity_document_id,
                    'document_no' => $document->document_no,
                    'issue_date' => $document->issue_date,
                    'expiry_date' => $document->expiry_date,
                    'issued_by' => $document->issued_by,
                    'data' => $document->data,
                    'status' => $document->status,
                    'file_name' => $document->file_name_without_extension ?: $document->file_name,
                    'deleted_at' => optional($document->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($document->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.customer-documents.show', $document->id),
                    'edit_url' => route('admin.customer-documents.edit', $document->id),
                    'delete_url' => route('admin.customer-documents.delete', $document->id),
                    'restore_url' => route('admin.customer-documents.restore', $document->id),
                    'permissions' => [
                        'can_view' => $authUser->can('CustomerDocument_ViewAll') || $authUser->can('CustomerDocument_View'),
                        'can_edit' => $authUser->can('CustomerDocument_Edit'),
                        'can_delete' => $authUser->can('CustomerDocument_Delete'),
                        'can_restore' => $authUser->can('CustomerDocument_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Customer documents fetched successfully.',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $documents->currentPage(),
                        'last_page' => $documents->lastPage(),
                        'total' => $documents->total(),
                        'from' => $documents->firstItem(),
                        'to' => $documents->lastItem(),
                    ],
                ],
            ]);
        }

        return view('admin.customer-documents.index', compact('documents', 'search', 'isDeleted'));
    }

    public function create()
    {
        return view('admin.customer-documents.create', ['document' => null, 'customers' => Customer::orderBy('first_name')->orderBy('last_name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $document = new CustomerDocument($this->documentData($request, $validated));
        $document->created_by = Auth::id();
        $document->save();

        return redirect()->route('admin.customer-documents')->with('success', 'Customer document added successfully.');
    }

    public function show(int $id)
    {
        $document = CustomerDocument::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'customer'])->findOrFail($id);
        return view('admin.customer-documents.show', compact('document'));
    }

    public function edit(int $id)
    {
        $document = CustomerDocument::findOrFail($id);
        return view('admin.customer-documents.edit', ['document' => $document, 'customers' => Customer::orderBy('first_name')->orderBy('last_name')->get()]);
    }

    public function update(Request $request, int $id)
    {
        $document = CustomerDocument::findOrFail($id);
        $validated = $request->validate($this->rules(false));
        $data = $this->documentData($request, $validated, $document);
        $data['updated_by'] = Auth::id();
        $document->update($data);

        return redirect()->route('admin.customer-documents')->with('success', 'Customer document updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $document = CustomerDocument::findOrFail($id);
        $document->update(['deleted_by' => Auth::id()]);
        $document->delete();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Customer document deleted successfully.']);
        }

        return redirect()->route('admin.customer-documents')->with('success', 'Customer document deleted successfully.');
    }

    public function restore(Request $request, int $id)
    {
        $document = CustomerDocument::onlyTrashed()->findOrFail($id);
        $document->restore();
        $document->update(['deleted_by' => null, 'updated_by' => Auth::id()]);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Customer document restored successfully.']);
        }

        return redirect()->route('admin.customer-documents')->with('success', 'Customer document restored successfully.');
    }

    private function rules(bool $requiredFile = true): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,customer_id'],
            'identity_name' => ['required', 'string', 'max:191'],
            'identity_document_id' => ['nullable', 'integer'],
            'data' => ['nullable', 'string', 'max:191'],
            'document_no' => ['nullable', 'string', 'max:191'],
            'issued_by' => ['nullable', 'string', 'max:191'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,approved'],
            'document' => [$requiredFile ? 'required' : 'nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
        ];
    }

    private function documentData(Request $request, array $validated, ?CustomerDocument $existing = null): array
    {
        // Keep readonly identity_name and the original customer snapshot unchanged on update.
        $data = collect($validated)->except(['document', 'identity_name', 'identity_document_id', 'data'])->all();

        if (!$existing) {
            $customer = Customer::where('customer_id', $validated['customer_id'])->firstOrFail();
            $data['identity_name'] = $validated['identity_name'];
        }

        if ($request->hasFile('document')) {
            if ($existing?->path || $existing?->document) {
                Storage::disk('public')->delete($existing->path ?: $existing->document);
            }
            $file = $request->file('document');
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();
            $fileContents = file_get_contents($file->getRealPath());

            $data['path'] = $file->storeAs('customer_documents/' . now()->format('FY'), $fileName, 'public');
            $data['document'] = base64_encode($fileContents);
            $data['data'] = $file->getMimeType();
            $data['file_name'] = $fileName;
            $data['file_name_without_extension'] = $baseName;
        }

        return $data;
    }
}
