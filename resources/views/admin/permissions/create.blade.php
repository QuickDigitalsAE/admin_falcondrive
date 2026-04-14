@extends('admin.layouts.app')

@section('title', 'Create Permission')
@section('page_title', 'Create Permission')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.permissions') }}" class="transition hover:text-[#9b7a28]">Permissions</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Permission</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Permissions Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Create Permission Group</h1>
                            <p class="mt-1 text-sm text-slate-500">Select one module and multiple actions to create permissions in one go.</p>
                        </div>
                        <a href="{{ route('admin.permissions') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>

                <form action="{{ route('admin.permissions.store') }}" method="POST" class="space-y-6 p-4 sm:p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <div class="space-y-2">
                            <label for="module_name" class="text-sm font-semibold text-slate-700">Module Name</label>
                            <input id="module_name" type="text" name="module_name" value="{{ old('module_name') }}" placeholder="User"
                                class="w-full rounded-[18px] border {{ $errors->has('module_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                            <p class="text-xs text-slate-500">Example: `User`, `Role`, `Permissions`.</p>
                            @error('module_name')
                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="table_name" class="text-sm font-semibold text-slate-700">Table Name</label>
                            <select id="table_name" name="table_name"
                                class="w-full rounded-[18px] border {{ $errors->has('table_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                <option value="">--select table name--</option>
                                @foreach($tableOptions as $tableName)
                                    <option value="{{ $tableName }}" {{ old('table_name') === $tableName ? 'selected' : '' }}>
                                        {{ $tableName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('table_name')
                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Select Actions</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">Create Multiple Permissions</h3>
                        <p class="mt-1 text-sm text-slate-500">Checked actions will be inserted as `Module_Action` in one request.</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach($actionOptions as $action)
                                <label class="inline-flex items-center gap-3 rounded-2xl border border-[#eadfbe] bg-[#fffdf8] px-4 py-3 text-sm font-medium text-slate-700">
                                    <input type="checkbox" name="actions[]" value="{{ $action }}" class="h-4 w-4 rounded border-[#d4bc78] text-[#c79a2b] focus:ring-[#d6ab3d]"
                                        {{ in_array($action, old('actions', []), true) ? 'checked' : '' }}>
                                    <span>{{ $action }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error('actions')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                        @error('actions.*')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-5 rounded-2xl border border-dashed border-[#eadfbe] bg-[#fffaf0] px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Preview</p>
                            <div id="permissionBatchPreview" class="mt-2 flex flex-wrap gap-2 text-sm text-slate-700">
                                <span class="text-slate-400">Select module and actions to preview permission names.</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>Create Selected Permissions</button>
                        <a href="{{ route('admin.permissions') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moduleInput = document.getElementById('module_name');
            const checkboxes = Array.from(document.querySelectorAll('input[name="actions[]"]'));
            const preview = document.getElementById('permissionBatchPreview');

            function normalizeModule(value) {
                return (value || '')
                    .trim()
                    .replace(/[^A-Za-z0-9]+/g, ' ')
                    .split(' ')
                    .filter(Boolean)
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join('');
            }

            function renderPreview() {
                const moduleName = normalizeModule(moduleInput?.value || '');
                const actions = checkboxes.filter(cb => cb.checked).map(cb => cb.value);

                if (!moduleName || actions.length === 0) {
                    preview.innerHTML = '<span class="text-slate-400">Select module and actions to preview permission names.</span>';
                    return;
                }

                preview.innerHTML = actions
                    .map(action => `<span class="inline-flex rounded-full bg-[#f7edd0] px-3 py-1 text-xs font-semibold text-[#8a6a1c]">${moduleName}_${action}</span>`)
                    .join('');
            }

            moduleInput?.addEventListener('input', renderPreview);
            checkboxes.forEach(cb => cb.addEventListener('change', renderPreview));
            renderPreview();
        });
    </script>
@endpush
