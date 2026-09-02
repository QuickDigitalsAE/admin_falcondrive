@php
    $isEdit = !empty($document?->id);
    $textFields = [
        ['identity_name', 'Document name', 'text'],
        ['document_no', 'Document number', 'text'],
        ['issued_by', 'Issued by', 'text'],
    ];

    $currentFileName = $document?->file_name ?: '';
    $currentExtension = strtolower(pathinfo($currentFileName, PATHINFO_EXTENSION));
    $currentDocumentUrl = $document?->path
        ? asset('storage/' . $document->path)
        : ($document?->document && str_starts_with($document->document, 'data:')
            ? $document->document
            : ($document?->document && $document?->data
                ? 'data:' . $document->data . ';base64,' . $document->document
                : ($document?->document ? asset('storage/' . $document->document) : null)));
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="relative xl:col-span-2">
        <select id="customer_id" name="customer_id" required
            class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 pb-2 pt-6 text-sm text-slate-800 outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
            <option value="">Select customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->customer_id }}" @selected((string) old('customer_id', $document?->customer_id) === (string) $customer->customer_id)>
                    {{ trim($customer->first_name . ' ' . $customer->last_name) ?: 'Unnamed customer' }}
                    (ID: {{ $customer->customer_id }}, {{ $customer->email }})
                </option>
            @endforeach
        </select>
        <label for="customer_id" class="pointer-events-none absolute left-4 top-2.5 bg-[#fffdf8] px-1 text-xs font-medium text-slate-500">
            Customer
        </label>
        @error('customer_id')
            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @foreach ($textFields as [$name, $label, $type])
        <div>
            <div class="relative">
                <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                    value="{{ old($name, $document?->$name) }}" placeholder=" "
                    @if ($name === 'identity_name') readonly @endif
                    class="peer w-full rounded-[18px] border border-[#e5d7b1] {{ $name === 'identity_name' ? 'cursor-not-allowed bg-[#f5f1e5] text-slate-500' : 'bg-[#fffdf8]' }} px-4 pb-2 pt-6 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                <label for="{{ $name }}" class="pointer-events-none absolute left-4 top-2.5 bg-[#fffdf8] px-1 text-xs font-medium text-slate-500">
                    {{ $label }}{{ $name === 'identity_name' ? ' *' : '' }}
                </label>
            </div>
            @error($name)
                <p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    @foreach ([['issue_date', 'Issue date'], ['expiry_date', 'Expiry date']] as [$name, $label])
        <div>
            <label for="{{ $name }}" class="mb-2 block px-1 text-xs font-medium text-slate-500">
                {{ $label }}
            </label>
            <input id="{{ $name }}" name="{{ $name }}" type="date"
                value="{{ old($name, $document?->$name) }}"
                class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
            @error($name)
                <p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div>
        <label for="status" class="mb-2 block px-1 text-xs font-medium text-slate-500">Status</label>
        <select id="status" name="status"
            class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
            <option value="pending" @selected(old('status', $document?->status ?? 'pending') === 'pending')>Pending</option>
            <option value="approved" @selected(old('status', $document?->status) === 'approved')>Approved</option>
        </select>
    </div>

    <div>
        <label for="document" class="mb-2 block px-1 text-xs font-medium text-slate-500">
            Document file{{ $isEdit ? ' (leave blank to keep current)' : '' }}
        </label>
        <input id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp"
            class="block w-full rounded-[18px] border border-dashed border-[#d8c79d] bg-[#fffdf8] px-4 py-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f8e8b2] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#7d6220]">
        @error('document')
            <p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($isEdit)
        <div class="xl:col-span-2 rounded-2xl border border-[#eadfbe] bg-[#fffaf0] p-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#b89a4c]">Current Preview</p>
                    <p id="previewFileName" class="mt-1 text-sm text-slate-600">{{ $currentFileName ?: 'No file uploaded' }}</p>
                </div>
                <i class="fa-solid fa-file-lines text-xl text-[#b49543]"></i>
            </div>
            <div id="documentPreview" class="flex min-h-32 items-center justify-center overflow-hidden rounded-xl border border-[#eadfbe] bg-white p-3">
                @if ($currentDocumentUrl && in_array($currentExtension, ['jpg', 'jpeg', 'png', 'webp']))
                    <img src="{{ $currentDocumentUrl }}" alt="Document preview" class="max-h-72 max-w-full rounded-lg object-contain">
                @elseif ($currentDocumentUrl && $currentExtension === 'pdf')
                    <iframe src="{{ $currentDocumentUrl }}" title="Document preview" class="h-72 w-full rounded-lg border-0"></iframe>
                @elseif ($currentDocumentUrl)
                    <a href="{{ $currentDocumentUrl }}" target="_blank" class="text-sm font-semibold text-[#9b7a28] hover:underline">Open current document</a>
                @else
                    <span class="text-sm text-slate-400">No preview available.</span>
                @endif
            </div>
        </div>
    @endif

    <div class="xl:col-span-2">
        <label for="description" class="mb-2 block px-1 text-xs font-medium text-slate-500">Description</label>
        <textarea id="description" name="description" rows="4"
            class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('description', $document?->description) }}</textarea>
        @error('description')
            <p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex flex-wrap gap-3 border-t border-[#f0e6ca] pt-6">
    <button type="submit" class="inline-flex items-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white hover:bg-[#c59626]">
        <i class="fa-solid fa-floppy-disk mr-2"></i>
        {{ $isEdit ? 'Update Document' : 'Save Document' }}
    </button>
    <a href="{{ route('admin.customer-documents') }}" class="inline-flex items-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] hover:bg-[#fff8e8]">
        <i class="fa-solid fa-xmark mr-2"></i>
        Cancel
    </a>
</div>

@if ($isEdit)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const input = document.getElementById('document');
                const preview = document.getElementById('documentPreview');
                const fileName = document.getElementById('previewFileName');

                input?.addEventListener('change', function () {
                    const file = this.files?.[0];
                    if (!file) return;

                    fileName.textContent = file.name;
                    const reader = new FileReader();

                    reader.onload = function (event) {
                        const source = event.target.result;
                        preview.innerHTML = file.type === 'application/pdf'
                            ? `<iframe src="${source}" title="Document preview" class="h-72 w-full rounded-lg border-0"></iframe>`
                            : file.type.startsWith('image/')
                                ? `<img src="${source}" alt="Document preview" class="max-h-72 max-w-full rounded-lg object-contain">`
                                : `<a href="${source}" target="_blank" class="text-sm font-semibold text-[#9b7a28] hover:underline">Open selected document</a>`;
                    };

                    reader.readAsDataURL(file);
                });
            });
        </script>
    @endpush
@endif
