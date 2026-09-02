@extends('admin.layouts.app')

@section('title', 'Customers Documents')
@section('page_title', 'Customers Documents')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Customers Documents</span>
    </nav>
@endsection

@section('content')
    <div class="flex h-full flex-col gap-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input id="searchInput" type="text" placeholder="Search customer, document name or number"
                                class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2 sm:self-stretch">
                        <button id="searchBtn" type="button" class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626] sm:min-w-[110px]">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>
                            Search
                        </button>
                        <button id="resetBtn" type="button" class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]" title="Reset">
                            <i class="fa-solid fa-rotate-right text-[13px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    @can('CustomerDocument_Add')
                        <a href="{{ route('admin.customer-documents.create') }}" class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]">
                            <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                            Add Document
                        </a>
                    @endcan
                    @can('CustomerDocument_Delete')
                        <button id="trashToggleBtn" type="button" class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-red-300 bg-red-50 text-red-700 shadow-sm transition hover:bg-red-100" title="View Trash">
                            <i class="fa-solid fa-recycle text-[14px]"></i>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[#eee4ca] bg-white shadow-sm">
            <div class="theme-table-scroll min-h-0 flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-[#f2ead4]">
                    <thead class="sticky top-0 z-10 bg-[#fffaf0]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Document</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Number</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Expiry</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Status</th>
                        </tr>
                    </thead>
                    <tbody id="recordsTableBody" class="divide-y divide-[#f6f0df] bg-white">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">Loading customer documents...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex shrink-0 flex-col gap-3 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div id="tableMeta" class="text-sm text-slate-500">Showing 0 to 0 of 0 results</div>
                <div id="paginationWrapper" class="flex flex-wrap items-center gap-2"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const endpoint = @json(route('admin.customer-documents'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const resetBtn = document.getElementById('resetBtn');
            const trashToggleBtn = document.getElementById('trashToggleBtn');
            const recordsTableBody = document.getElementById('recordsTableBody');
            const paginationWrapper = document.getElementById('paginationWrapper');
            const tableMeta = document.getElementById('tableMeta');

            const params = new URLSearchParams(window.location.search);
            const state = {
                search: params.get('search') || '',
                isDeleted: params.get('is_deleted') === '1' ? 1 : 0,
                page: Math.max(1, Number.parseInt(params.get('page') || '1', 10)),
                loading: false,
                requestId: 0,
            };

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            function renderMeta(pagination) {
                tableMeta.textContent = `Showing ${pagination?.from ?? 0} to ${pagination?.to ?? 0} of ${pagination?.total ?? 0} results`;
            }

            function setLoading() {
                recordsTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <div class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-[#b49543]"></i>
                                <span>Loading customer documents...</span>
                            </div>
                        </td>
                    </tr>`;
            }

            function updateTrashUi() {
                if (!trashToggleBtn) return;

                const icon = trashToggleBtn.querySelector('i');
                const showingTrash = Number(state.isDeleted) === 1;

                trashToggleBtn.classList.toggle('border-red-300', !showingTrash);
                trashToggleBtn.classList.toggle('bg-red-50', !showingTrash);
                trashToggleBtn.classList.toggle('text-red-700', !showingTrash);
                trashToggleBtn.classList.toggle('border-green-300', showingTrash);
                trashToggleBtn.classList.toggle('bg-green-100', showingTrash);
                trashToggleBtn.classList.toggle('text-green-800', showingTrash);
                trashToggleBtn.title = showingTrash ? 'Back to Active' : 'View Trash';
                if (icon) icon.className = `fa-solid ${showingTrash ? 'fa-arrow-rotate-left' : 'fa-recycle'} text-[14px]`;
            }

            function syncUrl() {
                const url = new URL(window.location.href);
                state.search ? url.searchParams.set('search', state.search) : url.searchParams.delete('search');
                state.isDeleted ? url.searchParams.set('is_deleted', '1') : url.searchParams.delete('is_deleted');
                state.page > 1 ? url.searchParams.set('page', state.page) : url.searchParams.delete('page');
                window.history.replaceState({}, '', url.toString());
            }

            function renderPagination(pagination) {
                paginationWrapper.innerHTML = '';
                if (!pagination || pagination.last_page <= 1) return;

                const buttons = [];
                const addButton = (label, page, disabled, active = false) => buttons.push(`
                    <button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${active ? 'border-[#c79a2b] bg-[#c79a2b] text-white' : disabled ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${page}" ${disabled ? 'disabled' : ''}>${label}</button>`);

                addButton('Prev', pagination.current_page - 1, pagination.current_page === 1);
                for (let page = 1; page <= pagination.last_page; page++) {
                    if (page === 1 || page === pagination.last_page || Math.abs(page - pagination.current_page) <= 1) {
                        addButton(page, page, false, page === pagination.current_page);
                    } else if (Math.abs(page - pagination.current_page) === 2) {
                        buttons.push('<span class="px-1 text-slate-400">...</span>');
                    }
                }
                addButton('Next', pagination.current_page + 1, pagination.current_page === pagination.last_page);
                paginationWrapper.innerHTML = buttons.join('');
            }

            function actionHtml(record) {
                const permissions = record.permissions || {};
                const buttons = [];

                if (permissions.can_view) {
                    buttons.push(`<a href="${record.show_url}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]" title="View"><i class="fa-solid fa-eye text-[13px]"></i></a>`);
                }
                if (!record.deleted_at && permissions.can_edit) {
                    buttons.push(`<a href="${record.edit_url}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d9c68f] bg-[#fff5d8] text-[#9b7a28] transition hover:bg-[#ffefc1]" title="Edit"><i class="fa-solid fa-pen text-[13px]"></i></a>`);
                }
                if (!record.deleted_at && permissions.can_delete) {
                    buttons.push(`<button type="button" class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100" data-url="${record.delete_url}" data-method="DELETE" data-action="delete" title="Delete"><i class="fa-solid fa-trash-can text-[13px]"></i></button>`);
                }
                if (record.deleted_at && permissions.can_restore) {
                    buttons.push(`<button type="button" class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100" data-url="${record.restore_url}" data-method="PUT" data-action="restore" title="Restore"><i class="fa-solid fa-recycle text-[13px]"></i></button>`);
                }

                return buttons.length ? `<div class="flex items-center gap-2">${buttons.join('')}</div>` : '<span class="text-xs text-slate-400">No Actions</span>';
            }

            function renderRows(items) {
                if (!items.length) {
                    recordsTableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No customer documents found.</td></tr>';
                    return;
                }

                recordsTableBody.innerHTML = items.map((record) => `
                    <tr class="transition hover:bg-[#fffdf7]">
                        <td class="px-6 py-4">${actionHtml(record)}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-slate-800">${escapeHtml(record.customer_name || 'Unknown')}</p>
                            <p class="text-xs text-slate-500">ID: ${escapeHtml(record.customer_id)}</p>
                            <p class="text-xs text-slate-400">${escapeHtml(record.customer_email || '')}</p>
                        </td>
                        <td class="px-6 py-4"><p class="text-sm font-semibold text-slate-700">${escapeHtml(record.identity_name || 'N/A')}</p><p class="text-xs text-slate-400">${escapeHtml(record.data || record.file_name || '')}</p></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${escapeHtml(record.document_no || 'N/A')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${escapeHtml(record.expiry_date || 'N/A')}</td>
                        <td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${record.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'}">${escapeHtml(record.status ? record.status.charAt(0).toUpperCase() + record.status.slice(1) : 'Pending')}</span></td>
                    </tr>`).join('');
            }

            async function fetchRecords() {
                if (state.loading) return;
                state.loading = true;
                const requestId = ++state.requestId;
                setLoading();
                updateTrashUi();

                const query = new URLSearchParams();
                if (state.search) query.set('search', state.search);
                if (state.isDeleted) query.set('is_deleted', '1');
                if (state.page > 1) query.set('page', state.page);

                try {
                    const response = await fetch(`${endpoint}?${query.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                    });
                    const result = await response.json();
                    if (requestId !== state.requestId) return;
                    if (!response.ok || !result.status) throw new Error(result.message || 'Unable to load customer documents.');

                    renderRows(result.data.items || []);
                    renderPagination(result.data.pagination || {});
                    renderMeta(result.data.pagination || {});
                    syncUrl();
                } catch (error) {
                    recordsTableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-red-500">${escapeHtml(error.message || 'Something went wrong.')}</td></tr>`;
                    paginationWrapper.innerHTML = '';
                    renderMeta({});
                } finally {
                    state.loading = false;
                }
            }

            async function submitAction(button) {
                const isDelete = button.dataset.action === 'delete';
                const confirmation = await Swal.fire({
                    title: isDelete ? 'Delete Customer Document?' : 'Restore Customer Document?',
                    text: isDelete ? 'This document will move to trash.' : 'This document will become active again.',
                    icon: isDelete ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: isDelete ? 'Yes, Delete' : 'Yes, Restore',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    confirmButtonColor: isDelete ? '#dc2626' : '#16a34a',
                });
                if (!confirmation.isConfirmed) return;

                button.disabled = true;
                try {
                    const response = await fetch(button.dataset.url, {
                        method: button.dataset.method,
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                    });
                    const result = await response.json();
                    if (!response.ok || !result.status) throw new Error(result.message || 'Action failed.');
                    await Swal.fire({ icon: 'success', title: 'Done', text: result.message, timer: 1300, showConfirmButton: false });
                    fetchRecords();
                } catch (error) {
                    button.disabled = false;
                    Swal.fire({ icon: 'error', title: 'Unable to complete action', text: error.message });
                }
            }

            searchInput.value = state.search;
            searchBtn.addEventListener('click', () => { state.search = searchInput.value.trim(); state.page = 1; fetchRecords(); });
            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    state.search = searchInput.value.trim();
                    state.page = 1;
                    fetchRecords();
                }
            });
            resetBtn.addEventListener('click', () => {
                state.search = '';
                state.isDeleted = 0;
                state.page = 1;
                searchInput.value = '';
                fetchRecords();
            });
            trashToggleBtn?.addEventListener('click', () => {
                state.isDeleted = Number(state.isDeleted) === 1 ? 0 : 1;
                state.page = 1;
                fetchRecords();
            });
            paginationWrapper.addEventListener('click', (event) => {
                const button = event.target.closest('.page-btn');
                const page = Number.parseInt(button?.dataset.page || '0', 10);
                if (!button || button.disabled || !page || page === state.page) return;
                state.page = page;
                fetchRecords();
            });
            recordsTableBody.addEventListener('click', (event) => {
                const button = event.target.closest('.action-btn');
                if (button) submitAction(button);
            });

            fetchRecords();
        });
    </script>
@endpush
