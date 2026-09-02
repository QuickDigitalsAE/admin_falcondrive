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

    public function destroy(int $id)
    {
        $document = CustomerDocument::findOrFail($id);
        $document->update(['deleted_by' => Auth::id()]);
        $document->delete();
        return redirect()->route('admin.customer-documents')->with('success', 'Customer document deleted successfully.');
    }

    public function restore(int $id)
    {
        $document = CustomerDocument::onlyTrashed()->findOrFail($id);
        $document->restore();
        $document->update(['deleted_by' => null, 'updated_by' => Auth::id()]);
        return redirect()->route('admin.customer-documents')->with('success', 'Customer document restored successfully.');
    }

    private function rules(bool $requiredFile = true): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'identity_name' => ['required', 'string', 'max:191'],
            'identity_document_id' => ['nullable', 'integer'],
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
        $data = collect($validated)->except('document')->all();
        $customer = Customer::findOrFail($validated['customer_id']);
        $data['customer_details'] = [
            'id' => $customer->id,
            'customerId' => $customer->customer_id,
            'firstName' => $customer->first_name,
            'lastName' => $customer->last_name,
            'email' => $customer->email,
        ];

        if ($request->hasFile('document')) {
            if ($existing?->document) {
                Storage::disk('public')->delete($existing->document);
            }
            $file = $request->file('document');
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = Str::random(22) . '.' . $file->getClientOriginalExtension();
            $data['document'] = $file->storeAs('customer_documents/' . now()->format('FY'), $fileName, 'public');
            $data['file_name'] = $fileName;
            $data['file_name_without_extension'] = $baseName;
        }

        return $data;
    }
}
