@extends('admin.layouts.app')

@section('title', 'About Us')
@section('page_title', 'About Us')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">About Us</span>
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
                            <input type="text" id="searchInput" placeholder="Search by SEO titles or content"
                                class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 sm:self-stretch">
                        <button type="button" id="searchBtn"
                            class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626] sm:min-w-[110px]">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>
                            Search
                        </button>

                        <button type="button" id="resetBtn"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="Reset">
                            <i class="fa-solid fa-rotate-right text-[13px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    @can('AboutUs_Add')
                        <a href="{{ route('admin.about-us.create') }}"
                            class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]">
                            <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                            Add Record
                        </a>
                    @endcan
                    @can('AboutUs_Delete')
                        <button type="button" id="trashToggleBtn"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]"
                            title="View Trash">
                            <i class="fa-solid fa-recycle text-[14px]"></i>
                        </button>
                    @endcan
                    @can('AboutUs_ViewAll')
                        <a id="exportCsvBtn" href="{{ route('admin.about-us', ['is_export' => 1]) }}"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]"
                            title="Export CSV">
                            <i class="fa-solid fa-file-csv text-[14px]"></i>
                        </a>
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
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">SEO Title EN</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">SEO Title AR</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Content Preview</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Created</th>
                        </tr>
                    </thead>

                    <tbody id="aboutUsTableBody" class="divide-y divide-[#f6f0df] bg-white">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">Loading records...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="shrink-0 flex flex-col gap-3 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div id="tableMeta" class="text-sm text-slate-500">Showing 0 to 0 of 0 results</div>
                <div id="paginationWrapper" class="flex flex-wrap items-center gap-2"></div>
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
            const endpoint = @json(route('admin.about-us'));
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const resetBtn = document.getElementById('resetBtn');
            const trashToggleBtn = document.getElementById('trashToggleBtn');
            const exportCsvBtn = document.getElementById('exportCsvBtn');
            const aboutUsTableBody = document.getElementById('aboutUsTableBody');
            const paginationWrapper = document.getElementById('paginationWrapper');
            const tableMeta = document.getElementById('tableMeta');
            const actionForm = document.getElementById('actionForm');
            const actionFormMethod = document.getElementById('actionFormMethod');

            let state = {
                search: '',
                is_deleted: 0,
                page: 1,
                loading: false,
                requestId: 0,
            };

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function actionsHtml(item) {
                const permissions = item.permissions || {};
                const buttons = [];

                if (permissions.can_view) {
                    buttons.push(`
                        <a href="${item.show_url}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="View">
                            <i class="fa-solid fa-eye text-[13px]"></i>
                        </a>
                    `);
                }

                if (!item.deleted_at && permissions.can_edit) {
                    buttons.push(`
                        <a href="${item.edit_url}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d9c68f] bg-[#fff5d8] text-[#9b7a28] transition hover:bg-[#ffefc1]"
                            title="Edit">
                            <i class="fa-solid fa-pen text-[13px]"></i>
                        </a>
                    `);
                }

                if (!item.deleted_at && permissions.can_delete) {
                    buttons.push(`
                        <button type="button"
                            class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                            data-url="${item.delete_url}"
                            data-method="DELETE"
                            data-confirm="Are you sure you want to delete this record?"
                            data-action-type="delete"
                            title="Delete">
                            <i class="fa-solid fa-trash-can text-[13px]"></i>
                        </button>
                    `);
                }

                if (item.deleted_at && permissions.can_restore) {
                    buttons.push(`
                        <button type="button"
                            class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                            data-url="${item.restore_url}"
                            data-method="PUT"
                            data-confirm="Are you sure you want to restore this record?"
                            data-action-type="restore"
                            title="Restore">
                            <i class="fa-solid fa-recycle text-[13px]"></i>
                        </button>
                    `);
                }

                return buttons.length
                    ? `<div class="flex items-center gap-2">${buttons.join('')}</div>`
                    : '<span class="text-xs text-slate-400">No Actions</span>';
            }

            function renderRows(items) {
                if (!items.length) {
                    aboutUsTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">No records found.</td>
                        </tr>
                    `;
                    return;
                }

                aboutUsTableBody.innerHTML = items.map(item => `
                    <tr class="hover:bg-[#fffdf7] transition">
                        <td class="px-6 py-4">${actionsHtml(item)}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-800">${escapeHtml(item.seo_title_en)}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${escapeHtml(item.seo_title_ar)}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-700">${escapeHtml(item.first_section_preview)}</p>
                            <p class="mt-1 text-xs text-slate-500">${escapeHtml(item.mission_preview)}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">${window.renderSuperAdminAuditStamp ? window.renderSuperAdminAuditStamp(item, escapeHtml) : escapeHtml(item.created_at_human || '')}</td>
                    </tr>
                `).join('');
            }

            function renderPagination(pagination) {
                paginationWrapper.innerHTML = '';

                if (!pagination || pagination.last_page <= 1) {
                    return;
                }

                const buttons = [];

                buttons.push(`
                    <button type="button"
                        class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === 1 ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                        data-page="${pagination.current_page - 1}"
                        ${pagination.current_page === 1 ? 'disabled' : ''}>
                        Prev
                    </button>
                `);

                for (let page = 1; page <= pagination.last_page; page++) {
                    if (page === 1 || page === pagination.last_page || (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)) {
                        buttons.push(`
                            <button type="button"
                                class="page-btn rounded-lg border px-3 py-2 text-sm ${page === pagination.current_page ? 'border-[#c79a2b] bg-[#c79a2b] text-white' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                                data-page="${page}">
                                ${page}
                            </button>
                        `);
                    } else if (page === pagination.current_page - 2 || page === pagination.current_page + 2) {
                        buttons.push('<span class="px-1 text-slate-400">...</span>');
                    }
                }

                buttons.push(`
                    <button type="button"
                        class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === pagination.last_page ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                        data-page="${pagination.current_page + 1}"
                        ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                        Next
                    </button>
                `);

                paginationWrapper.innerHTML = buttons.join('');
            }

            function renderMeta(pagination) {
                const from = pagination?.from ?? 0;
                const to = pagination?.to ?? 0;
                const total = pagination?.total ?? 0;
                tableMeta.textContent = `Showing ${from} to ${to} of ${total} results`;
            }

            function setLoading() {
                aboutUsTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-[#b49543]"></i>
                                <span>Loading records...</span>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function updateTrashUI() {
                if (!trashToggleBtn) return;

                if (Number(state.is_deleted) === 1) {
                    trashToggleBtn.classList.remove('border-red-300', 'bg-red-50', 'text-red-700');
                    trashToggleBtn.classList.add('border-green-300', 'bg-green-100', 'text-green-800');
                    trashToggleBtn.setAttribute('title', 'Back to Active');
                } else {
                    trashToggleBtn.classList.add('border-red-300', 'bg-red-50', 'text-red-700');
                    trashToggleBtn.classList.remove('border-green-300', 'bg-green-100', 'text-green-800');
                    trashToggleBtn.setAttribute('title', 'View Trash');
                }
            }

            function updateExportUrl() {
                if (!exportCsvBtn) return;

                const params = new URLSearchParams();
                if (state.search) params.set('search', state.search);
                if (Number(state.is_deleted) === 1) params.set('is_deleted', '1');
                params.set('is_export', '1');
                exportCsvBtn.href = `${endpoint}?${params.toString()}`;
            }

            async function fetchRecords() {
                if (state.loading) return;

                state.loading = true;
                state.requestId += 1;
                const currentRequestId = state.requestId;

                setLoading();
                updateTrashUI();
                updateExportUrl();

                const params = new URLSearchParams();
                if (state.search) params.set('search', state.search);
                if (Number(state.is_deleted) === 1) params.set('is_deleted', '1');
                if (state.page > 1) params.set('page', state.page);

                try {
                    const response = await fetch(`${endpoint}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (currentRequestId !== state.requestId) return;
                    if (!response.ok || !result.status) {
                        throw new Error(result.message || 'Failed to fetch records.');
                    }

                    renderRows(result.data.items || []);
                    renderPagination(result.data.pagination || {});
                    renderMeta(result.data.pagination || {});

                    const url = new URL(window.location.href);

                    if (state.search) {
                        url.searchParams.set('search', state.search);
                    } else {
                        url.searchParams.delete('search');
                    }

                    if (Number(state.is_deleted) === 1) {
                        url.searchParams.set('is_deleted', '1');
                    } else {
                        url.searchParams.delete('is_deleted');
                    }

                    if (state.page > 1) {
                        url.searchParams.set('page', state.page);
                    } else {
                        url.searchParams.delete('page');
                    }

                    window.history.replaceState({}, '', url.toString());
                } catch (error) {
                    aboutUsTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-red-500">
                                ${escapeHtml(error.message || 'Something went wrong.')}
                            </td>
                        </tr>
                    `;
                    paginationWrapper.innerHTML = '';
                    renderMeta({ from: 0, to: 0, total: 0 });
                } finally {
                    state.loading = false;
                }
            }

            async function submitAction(url, method, confirmText, actionType = 'default') {
                if (!url || !method) return;

                const isDelete = actionType === 'delete';
                const isRestore = actionType === 'restore';

                const result = await Swal.fire({
                    title: isDelete ? 'Delete Record?' : isRestore ? 'Restore Record?' : 'Are you sure?',
                    text: confirmText,
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
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl px-5 py-2',
                        cancelButton: 'rounded-xl px-5 py-2',
                    }
                });

                if (!result.isConfirmed) return;

                actionForm.action = url;
                actionFormMethod.value = method;
                actionForm.submit();
            }

            const urlParams = new URLSearchParams(window.location.search);
            state.search = urlParams.get('search') || '';
            state.is_deleted = urlParams.get('is_deleted') === '1' ? 1 : 0;
            state.page = parseInt(urlParams.get('page') || '1', 10);
            searchInput.value = state.search;

            searchBtn.addEventListener('click', function () {
                state.search = searchInput.value.trim();
                state.page = 1;
                fetchRecords();
            });

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    state.search = searchInput.value.trim();
                    state.page = 1;
                    fetchRecords();
                }
            });

            resetBtn.addEventListener('click', function () {
                state.search = '';
                state.is_deleted = 0;
                state.page = 1;
                searchInput.value = '';
                fetchRecords();
            });

            if (trashToggleBtn) {
                trashToggleBtn.addEventListener('click', function () {
                    state.is_deleted = Number(state.is_deleted) === 1 ? 0 : 1;
                    state.page = 1;
                    fetchRecords();
                });
            }

            paginationWrapper.addEventListener('click', function (event) {
                const btn = event.target.closest('.page-btn');
                if (!btn || btn.disabled) return;

                const page = parseInt(btn.dataset.page || '1', 10);
                if (!page || page < 1 || page === state.page) return;

                state.page = page;
                fetchRecords();
            });

            aboutUsTableBody.addEventListener('click', function (event) {
                const btn = event.target.closest('.action-btn');
                if (!btn) return;

                submitAction(
                    btn.dataset.url,
                    btn.dataset.method,
                    btn.dataset.confirm,
                    btn.dataset.actionType || 'default'
                );
            });

            fetchRecords();
        });
    </script>
@endpush

