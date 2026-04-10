@extends('admin.layouts.app')

@section('title', 'Users')
@section('page_title', 'Users')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Users</span>
    </nav>
@endsection

@section('content')
    <div class="flex h-full flex-col gap-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
            <!-- Minimal Filters -->
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input type="text" id="searchInput"
                                placeholder="Search by name, email, phone, emp id, cnic or passport"
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

            <!-- Top Action Bar -->
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    @can('User_Add')
                    <a href="{{ route('admin.users.create') }}"
                        class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]">
                        <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                        Add User
                    </a>
                    @endcan
                    @can('User_Delete')
                    <button type="button" id="trashToggleBtn"
                        class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]"
                        title="View Trash">
                        <i class="fa-solid fa-recycle text-[14px]"></i>
                    </button>
                    @endcan
                    @can('User_Add')
                    <a id="exportCsvBtn" href="{{ route('admin.users', ['is_export' => 1]) }}"
                        class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]"
                        title="Export CSV">
                        <i class="fa-solid fa-file-csv text-[14px]"></i>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[#eee4ca] bg-white shadow-sm">
            <div class="theme-table-scroll min-h-0 flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-[#f2ead4]">
                    <thead class="sticky top-0 z-10 bg-[#fffaf0]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Created</th>
                        </tr>
                    </thead>

                    <tbody id="usersTableBody" class="divide-y divide-[#f6f0df] bg-white">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Loading users...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="shrink-0 flex flex-col gap-3 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div id="tableMeta" class="text-sm text-slate-500">
                    Showing 0 to 0 of 0 results
                </div>

                <div id="paginationWrapper" class="flex flex-wrap items-center gap-2"></div>
            </div>
        </div>

    <form id="actionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="actionFormMethod" value="POST">
    </form>
@endsection

@push('scripts')
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const endpoint = @json(route('admin.users'));

            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const resetBtn = document.getElementById('resetBtn');
            const trashToggleBtn = document.getElementById('trashToggleBtn');
            const trashToggleText = document.getElementById('trashToggleText');
            const exportCsvBtn = document.getElementById('exportCsvBtn');
            const usersTableBody = document.getElementById('usersTableBody');
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

            function avatarHtml(user) {
                const avatar = user.profile_image_url
                    ? user.profile_image_url
                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=f2d46b&color=222`;

                return `
                    <div class="flex items-center gap-3">
                        <img src="${avatar}" alt="${escapeHtml(user.name)}" class="h-10 w-10 rounded-full object-cover ring-2 ring-[#f4e3ab]">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">${escapeHtml(user.name)}</p>
                            <p class="truncate text-sm text-slate-500">${escapeHtml(user.email)}</p>
                        </div>
                    </div>
                `;
            }

            function roleHtml(user) {
                if (!user.roles || !user.roles.length) {
                    return `<span class="text-sm text-slate-400">No Role</span>`;
                }

                return user.roles.map(role => `
                    <span class="mr-1 inline-flex rounded-full bg-[#f7edd0] px-2.5 py-1 text-xs font-medium text-[#8a6a1c]">
                        ${escapeHtml(role.name)}
                    </span>
                `).join('');
            }

            function statusHtml(user) {
                if (user.deleted_at) {
                    return `<span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Deleted</span>`;
                }

                if (Number(user.status) === 1) {
                    return `<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Active</span>`;
                }

                return `<span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Inactive</span>`;
            }

            function actionsHtml(user) {
                const permissions = user.permissions || {};
                const buttons = [];

                if (permissions.can_view) {
                    buttons.push(`
                        <a href="${user.show_url}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="View">
                            <i class="fa-solid fa-eye text-[13px]"></i>
                        </a>
                    `);
                }

                if (permissions.can_edit) {
                    buttons.push(`
                        <a href="${user.edit_url}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d9c68f] bg-[#fff5d8] text-[#9b7a28] transition hover:bg-[#ffefc1]"
                            title="Edit">
                            <i class="fa-solid fa-pen text-[13px]"></i>
                        </a>
                    `);
                }

                if (!user.deleted_at && permissions.can_delete) {
                    buttons.push(`
                        <button type="button"
                            class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                            data-url="${user.delete_url}"
                            data-method="DELETE"
                            data-confirm="Are you sure you want to delete this user?"
                            data-action-type="delete"
                            title="Delete">
                            <i class="fa-solid fa-trash-can text-[13px]"></i>
                        </button>
                    `);
                }

                if (user.deleted_at && permissions.can_restore) {
                    buttons.push(`
                        <button type="button"
                            class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                            data-url="${user.restore_url}"
                            data-method="PUT"
                            data-confirm="Are you sure you want to restore this user?"
                            data-action-type="restore"
                            title="Restore">
                            <i class="fa-solid fa-recycle text-[13px]"></i>
                        </button>
                    `);
                }

                if (!buttons.length) {
                    return `<span class="text-xs text-slate-400">No Actions</span>`;
                }

                return `<div class="flex items-center gap-2">${buttons.join('')}</div>`;
            }

            function renderRows(items) {
                if (!items.length) {
                    usersTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                No users found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                usersTableBody.innerHTML = items.map(user => `
                    <tr class="hover:bg-[#fffdf7] transition">
                        <td class="px-6 py-4">${actionsHtml(user)}</td>
                        <td class="px-6 py-4">${avatarHtml(user)}</td>
                        <td class="px-6 py-4">${roleHtml(user)}</td>
                        <td class="px-6 py-4">${statusHtml(user)}</td>
                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">${escapeHtml(user.created_at_human || '')}</td>
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
                    if (
                        page === 1 ||
                        page === pagination.last_page ||
                        (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)
                    ) {
                        buttons.push(`
                            <button type="button"
                                class="page-btn rounded-lg border px-3 py-2 text-sm ${page === pagination.current_page ? 'border-[#c79a2b] bg-[#c79a2b] text-white' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                                data-page="${page}">
                                ${page}
                            </button>
                        `);
                    } else if (
                        page === pagination.current_page - 2 ||
                        page === pagination.current_page + 2
                    ) {
                        buttons.push(`<span class="px-1 text-slate-400">...</span>`);
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
                usersTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-[#b49543]"></i>
                                <span>Loading users...</span>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function updateTrashUI() {
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
                const params = new URLSearchParams();

                if (state.search) params.set('search', state.search);
                if (Number(state.is_deleted) === 1) params.set('is_deleted', '1');
                params.set('is_export', '1');

                exportCsvBtn.href = `${endpoint}?${params.toString()}`;
            }

            async function fetchUsers() {
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
                        throw new Error(result.message || 'Failed to fetch users.');
                    }

                    renderRows(result.data.items || []);
                    renderPagination(result.data.pagination || {});
                    renderMeta(result.data.pagination || {});

                    const url = new URL(window.location.href);
                    url.searchParams.set('search', state.search || '');

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

                    if (!state.search) {
                        url.searchParams.delete('search');
                    }

                    window.history.replaceState({}, '', url.toString());
                } catch (error) {
                    usersTableBody.innerHTML = `
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

            function resetFilters() {
                state.search = '';
                state.is_deleted = 0;
                state.page = 1;
                searchInput.value = '';
                fetchUsers();
            }

            async function submitAction(url, method, confirmText, actionType = 'default') {
                if (!url || !method) return;

                if (confirmText) {
                    const isDelete = actionType === 'delete';
                    const isRestore = actionType === 'restore';

                    const result = await Swal.fire({
                        title: isDelete ? 'Delete User?' : isRestore ? 'Restore User?' : 'Are you sure?',
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
                }

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
                fetchUsers();
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    state.search = searchInput.value.trim();
                    state.page = 1;
                    fetchUsers();
                }
            });

            resetBtn.addEventListener('click', function () {
                resetFilters();
            });

            trashToggleBtn.addEventListener('click', function () {
                state.is_deleted = Number(state.is_deleted) === 1 ? 0 : 1;
                state.page = 1;
                fetchUsers();
            });

            paginationWrapper.addEventListener('click', function (e) {
                const btn = e.target.closest('.page-btn');
                if (!btn || btn.disabled) return;

                const page = parseInt(btn.dataset.page || '1', 10);
                if (!page || page < 1 || page === state.page) return;

                state.page = page;
                fetchUsers();
            });

            usersTableBody.addEventListener('click', function (e) {
                const btn = e.target.closest('.action-btn');
                if (!btn) return;

                submitAction(
                    btn.dataset.url,
                    btn.dataset.method,
                    btn.dataset.confirm,
                    btn.dataset.actionType || 'default'
                );
            });

            fetchUsers();
        });
    </script>
@endpush