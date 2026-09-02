@php($isEdit = !empty($document?->id))
<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="relative xl:col-span-2">
        <select id="customer_id" name="customer_id" required class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
            <option value="">Select customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected((string) old('customer_id', $document?->customer_id) === (string) $customer->id)>{{ trim($customer->first_name.' '.$customer->last_name) ?: 'Unnamed customer' }} ({{ $customer->email }})</option>
            @endforeach
        </select>
        <label for="customer_id" class="pointer-events-none absolute left-4 top-2.5 bg-[#fffdf8] px-1 text-xs font-medium text-slate-500">Customer</label>
        @error('customer_id')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
    </div>
    @foreach ([['identity_name','Document name'],['document_no','Document number'],['issued_by','Issued by'],['identity_document_id','Document type ID']] as [$name,$label])
        <div>
            <div class="relative"><input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $document?->$name) }}" type="{{ $name === 'identity_document_id' ? 'number' : 'text' }}" placeholder=" " class="peer w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 pt-6 pb-2 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]"><label for="{{ $name }}" class="pointer-events-none absolute left-4 top-2.5 bg-[#fffdf8] px-1 text-xs font-medium text-slate-500">{{ $label }}{{ $name === 'identity_name' ? ' *' : '' }}</label></div>
            @error($name)<p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
    @endforeach
    @foreach ([['issue_date','Issue date'],['expiry_date','Expiry date']] as [$name,$label])
        <div><label for="{{ $name }}" class="mb-2 block px-1 text-xs font-medium text-slate-500">{{ $label }}</label><input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $document?->$name) }}" type="date" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">@error($name)<p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror</div>
    @endforeach
    <div><label for="status" class="mb-2 block px-1 text-xs font-medium text-slate-500">Status</label><select id="status" name="status" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]"><option value="pending" @selected(old('status', $document?->status ?? 'pending') === 'pending')>Pending</option><option value="approved" @selected(old('status', $document?->status) === 'approved')>Approved</option></select></div>
    <div><label for="document" class="mb-2 block px-1 text-xs font-medium text-slate-500">Document file{{ $isEdit ? ' (leave blank to keep current)' : '' }}</label><input id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="block w-full rounded-[18px] border border-dashed border-[#d8c79d] bg-[#fffdf8] px-4 py-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f8e8b2] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#7d6220]">@error('document')<p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror</div>
    <div class="xl:col-span-2"><label for="description" class="mb-2 block px-1 text-xs font-medium text-slate-500">Description</label><textarea id="description" name="description" rows="4" class="w-full rounded-[18px] border border-[#e5d7b1] bg-[#fffdf8] px-4 py-3 text-sm outline-none focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('description', $document?->description) }}</textarea>@error('description')<p class="mt-2 px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror</div>
</div>
<div class="flex flex-wrap gap-3 border-t border-[#f0e6ca] pt-6"><button type="submit" class="inline-flex items-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2"></i>{{ $isEdit ? 'Update Document' : 'Save Document' }}</button><a href="{{ route('admin.customer-documents') }}" class="inline-flex items-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2"></i>Cancel</a></div>
