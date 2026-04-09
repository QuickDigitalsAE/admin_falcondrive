@extends('admin.layouts.app')

@section('title', 'My Users')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-800">My Users</h1>
                <p class="mt-1 text-sm text-slate-500">Manage users created by you</p>

                <nav class="mt-3 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.dashboard') }}" class="transition hover:text-slate-700">
                        <i class="fa-solid fa-house text-[12px]"></i>
                        <span class="ml-1">Dashboard</span>
                    </a>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                    <span class="font-medium text-slate-700">My Users</span>
                </nav>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="trashToggleBtn"
                    class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i class="fa-solid fa-trash-can mr-2 text-[13px]"></i>
                    <span id="trashToggleText">View Trash</span>
                </button>

                <a href="{{ route('admin.users.create') }}"
                    class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                    <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                    Add User
                </a>

                <a id="exportCsvBtn" href="{{ route('admin.my-users', ['is_export' => 1]) }}"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-file-csv mr-2 text-[13px]"></i>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                <div class="md:col-span-8">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Search</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input type="text" id="searchInput"
                            placeholder="Search by name, email, phone, emp id, cnic or passport"
                            class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-slate-400">
                    </div>
                </div>

                <div class="md:col-span-4 flex items-end gap-2">
                    <button type="button" id="searchBtn"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-slate-800">
                        <i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>
                        Search
                    </button>

                    <button type="button" id="resetBtn"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-right mr-2 text-[12px]"></i>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-slate-500">Created</th>
                        </tr>
                    </thead>

                    <tbody id="usersTableBody" class="divide-y divide-slate-100 bg-white">
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Loading users...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div id="tableMeta" class="text-sm text-slate-500">
                    Showing 0 to 0 of 0 results
                </div>

                <div id="paginationWrapper" class="flex flex-wrap items-center gap-2"></div>
            </div>
        </div>
    </div>

    <form id="actionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="actionFormMethod" value="POST">
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const endpoint = @json(route('admin.my-users'));

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
                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'User')}&background=0f172a&color=fff`;

                return `
                        <div class="flex items-center gap-3">
                            <img src="${avatar}" alt="${escapeHtml(user.name)}" class="h-10 w-10 rounded-full object-cover">
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
                        <span class="mr-1 inline-flex rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-700">
                            ${escapeHtml(role.name)}
                        </span>
                    `).join('');
            }

            function statusHtml(user) {
                if (user.deleted_at) {
                    return `<span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">Deleted</span>`;
                }

                if (Number(user.status) === 1) {
                    return `<span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">Active</span>`;
                }

                return `<span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Inactive</span>`;
            }

            function actionsHtml(user) {
                let deleteOrRestore = '';

                if (!user.deleted_at) {
                    deleteOrRestore = `
                            <button type="button"
                                class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                                data-url="${user.delete_url}"
                                data-method="DELETE"
                                data-confirm="Are you sure you want to delete this user?"
                                title="Delete">
                                <i class="fa-solid fa-trash-can text-[13px]"></i>
                            </button>
                        `;
                } else {
                    deleteOrRestore = `
                            <button type="button"
                                class="action-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                data-url="${user.restore_url}"
                                data-method="PUT"
                                data-confirm="Are you sure you want to restore this user?"
                                title="Restore">
                                <i class="fa-solid fa-rotate-left text-[13px]"></i>
                            </button>
                        `;
                }

                return `
                        <div class="flex items-center gap-2">
                            <a href="${user.show_url}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                                title="View">
                                <i class="fa-solid fa-eye text-[13px]"></i>
                            </a>

                            <a href="${user.edit_url}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-600 transition hover:bg-blue-100"
                                title="Edit">
                                <i class="fa-solid fa-pen text-[13px]"></i>
                            </a>

                            ${deleteOrRestore}
                        </div>
                    `;
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
                        <tr>
                            <td class="px-6 py-4">${actionsHtml(user)}</td>
                            <td class="px-6 py-4">${avatarHtml(user)}</td>
                            <td class="px-6 py-4">${roleHtml(user)}</td>
                            <td class="px-6 py-4">${statusHtml(user)}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">${escapeHtml(user.created_at_human || '')}</td>
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
                            class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === 1 ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'}"
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
                                    class="page-btn rounded-lg border px-3 py-2 text-sm ${page === pagination.current_page ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'}"
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
                            class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === pagination.last_page ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'}"
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
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <span>Loading users...</span>
                                </div>
                            </td>
                        </tr>
                    `;
            }

            function updateTrashUI() {
                if (Number(state.is_deleted) === 1) {
                    trashToggleText.textContent = 'Back to Active';
                    trashToggleBtn.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
                    trashToggleBtn.classList.remove('border-slate-300', 'bg-white', 'text-slate-700');
                } else {
                    trashToggleText.textContent = 'View Trash';
                    trashToggleBtn.classList.remove('border-red-200', 'bg-red-50', 'text-red-700');
                    trashToggleBtn.classList.add('border-slate-300', 'bg-white', 'text-slate-700');
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

            function submitAction(url, method, confirmText) {
                if (!url || !method) return;
                if (confirmText && !window.confirm(confirmText)) return;

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
                    btn.dataset.confirm
                );
            });

            fetchUsers();
        });
    </script>
@endpush