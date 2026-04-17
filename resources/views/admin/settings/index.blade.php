@extends('admin.layouts.app')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Settings</span>
    </nav>
@endsection

@section('content')
@php
    $orderedGroups = collect($groups)
        ->sortBy(function ($group) {
            return match (strtolower((string) $group)) {
                'site' => 0,
                'admin' => 1,
                default => 2,
            };
        })
        ->values()
        ->all();
@endphp
<div class="flex h-full flex-col gap-5">
    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
        <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_180px_auto]">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]"><i class="fa-solid fa-magnifying-glass text-sm"></i></span>
                    <input type="text" id="searchInput" placeholder="Search by key, display name, value, details, group or type" class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                </div>
                <select id="typeFilter" class="rounded-xl border border-[#eadfbe] bg-[#fffdf8] px-4 py-2.5 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                    <option value="">All Types</option>
                    @foreach ($settingTypes as $settingType)
                        <option value="{{ $settingType }}">{{ ucfirst(str_replace('_', ' ', $settingType)) }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <button type="button" id="searchBtn" class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626]"><i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>Search</button>
                    <button type="button" id="resetBtn" class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]" title="Reset"><i class="fa-solid fa-rotate-right text-[13px]"></i></button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
            <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                @can('Setting_Add')
                    <a href="{{ route('admin.settings.create') }}" class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]"><i class="fa-solid fa-plus mr-2 text-[13px]"></i>Add Setting</a>
                @endcan
                @can('Setting_Delete')
                    <button type="button" id="trashToggleBtn" class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]" title="View Trash"><i class="fa-solid fa-recycle text-[14px]"></i></button>
                @endcan
                <a id="exportCsvBtn" href="{{ route('admin.settings', ['is_export' => 1]) }}" class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]" title="Export CSV"><i class="fa-solid fa-file-csv text-[14px]"></i></a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5">
        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[#eee4ca] bg-white shadow-sm">
            @if (!empty($orderedGroups))
                <div class="border-b border-[#f2ead4] bg-[#fffdf9] px-6 py-4">
                    <div id="groupTabs" class="flex flex-wrap gap-2">
                        @foreach ($orderedGroups as $group)
                            <button
                                type="button"
                                class="group-tab inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition {{ $group === 'site' ? 'border-[#c79a2b] bg-[#fff1c8] text-[#8b6717]' : 'border-[#eadfbe] bg-white text-slate-600 hover:bg-[#fff8e7]' }}"
                                data-group-value="{{ $group }}">
                                {{ ucfirst($group) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="theme-table-scroll min-h-0 flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-[#f2ead4]">
                    <thead class="sticky top-0 z-10 bg-[#fffaf0]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Setting</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Value</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Created</th>
                        </tr>
                    </thead>
                    <tbody id="settingsTableBody" class="divide-y divide-[#f6f0df] bg-white">
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">Loading settings...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="shrink-0 flex flex-col gap-3 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div id="tableMeta" class="text-sm text-slate-500">Showing 0 to 0 of 0 results</div>
                <div id="paginationWrapper" class="flex flex-wrap items-center gap-2"></div>
            </div>
        </div>
    </div>

    <form id="actionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="actionFormMethod" value="POST">
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoint = @json(route('admin.settings'));
    const defaultGroup = @json(collect($orderedGroups)->first(function ($group) {
        return strtolower((string) $group) === 'site';
    }) ?? ($orderedGroups[0] ?? ''));
    const searchInput = document.getElementById('searchInput');
    const groupTabs = document.getElementById('groupTabs');
    const typeFilter = document.getElementById('typeFilter');
    const searchBtn = document.getElementById('searchBtn');
    const resetBtn = document.getElementById('resetBtn');
    const trashToggleBtn = document.getElementById('trashToggleBtn');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const settingsTableBody = document.getElementById('settingsTableBody');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const tableMeta = document.getElementById('tableMeta');
    const actionForm = document.getElementById('actionForm');
    const actionFormMethod = document.getElementById('actionFormMethod');
    let state = { search: '', group: defaultGroup, type: '', is_deleted: 0, page: 1, loading: false, requestId: 0 };

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }

    function renderMeta(p) {
        tableMeta.textContent = `Showing ${p?.from ?? 0} to ${p?.to ?? 0} of ${p?.total ?? 0} results`;
    }

    function syncGroupTabs() {
        document.querySelectorAll('.group-tab').forEach(function (button) {
            const isActive = (button.dataset.groupValue || '') === state.group;
            button.classList.toggle('border-[#c79a2b]', isActive);
            button.classList.toggle('bg-[#fff1c8]', isActive);
            button.classList.toggle('text-[#8b6717]', isActive);
            button.classList.toggle('border-[#eadfbe]', !isActive);
            button.classList.toggle('bg-white', !isActive);
            button.classList.toggle('text-slate-600', !isActive);
        });
    }

    function normalizePath(value) {
        if (value === null || value === undefined) return '';
        return String(value).replaceAll('\\', '/');
    }

    function getBaseName(value) {
        const normalized = normalizePath(value);
        if (!normalized) return '';
        const segments = normalized.split('/');
        return segments[segments.length - 1] || normalized;
    }

    function valuePreviewHtml(item) {
        const type = String(item.type || '');
        const value = item.value || '';
        const fullValue = item.value_full || value || '';
        const valueUrl = item.value_url || '';

        if (type === 'image' && valueUrl) {
            return `<div class="flex items-center gap-3">
                <img src="${escapeHtml(valueUrl)}" alt="${escapeHtml(item.display_name || 'Setting image')}" class="h-14 w-14 rounded-xl border border-[#eadfbe] bg-[#fffdf8] object-cover p-1">
                <div class="min-w-0">
                    <a href="${escapeHtml(valueUrl)}" target="_blank" class="inline-flex items-center text-xs font-semibold text-[#9b7a28] hover:text-[#7d6220]">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i>Open image
                    </a>
                    <p class="mt-1 truncate font-mono text-xs text-slate-500">${escapeHtml(getBaseName(fullValue))}</p>
                </div>
            </div>`;
        }

        if (type === 'file' && valueUrl) {
            return `<div class="flex items-center gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#fff4d6] text-[#8b6717]">
                    <i class="fa-solid fa-file-arrow-down"></i>
                </span>
                <div class="min-w-0">
                    <a href="${escapeHtml(valueUrl)}" target="_blank" class="inline-flex items-center text-xs font-semibold text-[#9b7a28] hover:text-[#7d6220]">
                        <i class="fa-solid fa-download mr-1 text-[10px]"></i>Open file
                    </a>
                    <p class="mt-1 truncate font-mono text-xs text-slate-500">${escapeHtml(getBaseName(fullValue))}</p>
                </div>
            </div>`;
        }

        return `<p class="max-w-[260px] whitespace-pre-wrap break-words text-sm text-slate-600">${escapeHtml(value)}</p>`;
    }

    function setLoading() {
        settingsTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500"><div class="inline-flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin text-[#b49543]"></i><span>Loading settings...</span></div></td></tr>`;
    }

    function updateTrashUI() {
        if (!trashToggleBtn) return;
        if (Number(state.is_deleted) === 1) {
            trashToggleBtn.classList.remove('border-red-300', 'bg-red-50', 'text-red-700');
            trashToggleBtn.classList.add('border-green-300', 'bg-green-100', 'text-green-800');
        } else {
            trashToggleBtn.classList.add('border-red-300', 'bg-red-50', 'text-red-700');
            trashToggleBtn.classList.remove('border-green-300', 'bg-green-100', 'text-green-800');
        }
    }

    function updateExportUrl() {
        const params = new URLSearchParams();
        if (state.search) params.set('search', state.search);
        if (state.group) params.set('group', state.group);
        if (state.type) params.set('type', state.type);
        if (Number(state.is_deleted) === 1) params.set('is_deleted', '1');
        params.set('is_export', '1');
        exportCsvBtn.href = `${endpoint}?${params.toString()}`;
    }

    function renderPagination(p) {
        paginationWrapper.innerHTML = '';
        if (!p || p.last_page <= 1) return;
        const buttons = [];
        buttons.push(`<button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${p.current_page === 1 ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${p.current_page - 1}" ${p.current_page === 1 ? 'disabled' : ''}>Prev</button>`);
        for (let page = 1; page <= p.last_page; page++) {
            if (page === 1 || page === p.last_page || (page >= p.current_page - 1 && page <= p.current_page + 1)) {
                buttons.push(`<button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${page === p.current_page ? 'border-[#c79a2b] bg-[#c79a2b] text-white' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${page}">${page}</button>`);
            } else if (page === p.current_page - 2 || page === p.current_page + 2) {
                buttons.push('<span class="px-1 text-slate-400">...</span>');
            }
        }
        buttons.push(`<button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${p.current_page === p.last_page ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${p.current_page + 1}" ${p.current_page === p.last_page ? 'disabled' : ''}>Next</button>`);
        paginationWrapper.innerHTML = buttons.join('');
    }

    function actionsHtml(item) {
        const permissions = item.permissions || {};
        const buttons = [];
        if (permissions.can_view) buttons.push(`<a href="${item.show_url}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]" title="View"><i class="fa-solid fa-eye text-[13px]"></i></a>`);
        if (permissions.can_edit) buttons.push(`<a href="${item.edit_url}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d9c68f] bg-[#fff5d8] text-[#9b7a28] transition hover:bg-[#ffefc1]" title="Edit"><i class="fa-solid fa-pen text-[13px]"></i></a>`);
        if (!item.deleted_at && permissions.can_delete) buttons.push(`<button type="button" class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100" data-url="${item.delete_url}" data-method="DELETE" data-confirm="Are you sure you want to delete this setting?" data-action-type="delete"><i class="fa-solid fa-trash-can text-[13px]"></i></button>`);
        if (item.deleted_at && permissions.can_restore) buttons.push(`<button type="button" class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100" data-url="${item.restore_url}" data-method="PUT" data-confirm="Are you sure you want to restore this setting?" data-action-type="restore"><i class="fa-solid fa-recycle text-[13px]"></i></button>`);
        return buttons.length ? `<div class="flex items-center gap-2">${buttons.join('')}</div>` : '<span class="text-xs text-slate-400">No Actions</span>';
    }

    function renderRows(items) {
        settingsTableBody.innerHTML = !items.length
            ? `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No settings found.</td></tr>`
            : items.map(item => `<tr class="hover:bg-[#fffdf7] transition">
                <td class="px-6 py-4">${actionsHtml(item)}</td>
                <td class="px-6 py-4">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">${escapeHtml(item.display_name)}</p>
                        <p class="mt-1 font-mono text-xs text-slate-500">${escapeHtml(item.key)}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            ${item.group ? `<span class="inline-flex rounded-full bg-[#fff4d6] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#7d6220]">${escapeHtml(item.group)}</span>` : ''}
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">Order ${escapeHtml(item.order)}</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">${valuePreviewHtml(item)}</td>
                <td class="px-6 py-4"><span class="inline-flex rounded-full border border-[#ead39a] bg-[#fff8e8] px-3 py-1 text-xs font-semibold text-[#8b6717]">${escapeHtml(item.type)}</span></td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">${escapeHtml(item.created_at_human || '')}</td>
            </tr>`).join('');
    }

    async function fetchSettings() {
        if (state.loading) return;
        state.loading = true;
        state.requestId += 1;
        const currentRequestId = state.requestId;
        setLoading();
        updateTrashUI();
        updateExportUrl();
        syncGroupTabs();

        const params = new URLSearchParams();
        if (state.search) params.set('search', state.search);
        if (state.group) params.set('group', state.group);
        if (state.type) params.set('type', state.type);
        if (Number(state.is_deleted) === 1) params.set('is_deleted', '1');
        if (state.page > 1) params.set('page', state.page);

        try {
            const response = await fetch(`${endpoint}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            const result = await response.json();
            if (currentRequestId !== state.requestId) return;
            if (!response.ok || !result.status) throw new Error(result.message || 'Failed to fetch settings.');
            renderRows(result.data.items || []);
            renderPagination(result.data.pagination || {});
            renderMeta(result.data.pagination || {});
        } catch (error) {
            settingsTableBody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-500">${escapeHtml(error.message || 'Something went wrong.')}</td></tr>`;
            paginationWrapper.innerHTML = '';
            renderMeta({ from: 0, to: 0, total: 0 });
        } finally {
            state.loading = false;
        }
    }

    async function submitAction(url, method, confirmText, actionType = 'default') {
        const isDelete = actionType === 'delete';
        const isRestore = actionType === 'restore';
        const result = await Swal.fire({
            title: isDelete ? 'Delete Setting?' : isRestore ? 'Restore Setting?' : 'Are you sure?',
            text: confirmText || 'Please confirm this action.',
            icon: isDelete ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: isDelete ? 'Yes, Delete' : isRestore ? 'Yes, Restore' : 'Yes, Continue',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            background: '#ffffff',
            color: '#1e293b',
            confirmButtonColor: isDelete ? '#dc2626' : '#16a34a',
            cancelButtonColor: '#94a3b8',
        });
        if (!result.isConfirmed) return;
        actionForm.action = url;
        actionFormMethod.value = method;
        actionForm.submit();
    }

    searchBtn.addEventListener('click', function () {
        state.search = searchInput.value.trim();
        state.type = typeFilter.value;
        state.page = 1;
        fetchSettings();
    });

    [searchInput, typeFilter].forEach(function (element) {
        element?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                state.search = searchInput.value.trim();
                state.type = typeFilter.value;
                state.page = 1;
                fetchSettings();
            }
        });
    });

    typeFilter?.addEventListener('change', function () {
        state.type = typeFilter.value;
        state.page = 1;
        fetchSettings();
    });

    resetBtn.addEventListener('click', function () {
        state.search = '';
        state.group = defaultGroup;
        state.type = '';
        state.is_deleted = 0;
        state.page = 1;
        searchInput.value = '';
        typeFilter.value = '';
        fetchSettings();
    });

    groupTabs?.addEventListener('click', function (event) {
        const button = event.target.closest('.group-tab');
        if (!button) {
            return;
        }

        const nextGroup = button.dataset.groupValue || '';
        if (nextGroup === state.group) {
            return;
        }

        state.group = nextGroup;
            state.page = 1;
        fetchSettings();
    });

    trashToggleBtn?.addEventListener('click', function () {
        state.is_deleted = Number(state.is_deleted) === 1 ? 0 : 1;
        state.page = 1;
        fetchSettings();
    });

    paginationWrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.page-btn');
        if (!btn || btn.disabled) return;
        const page = parseInt(btn.dataset.page || '1', 10);
        if (!page || page < 1 || page === state.page) return;
        state.page = page;
        fetchSettings();
    });

    settingsTableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.action-btn');
        if (!btn) return;
        submitAction(btn.dataset.url, btn.dataset.method, btn.dataset.confirm, btn.dataset.actionType || 'default');
    });

    fetchSettings();
});
</script>
@endpush
