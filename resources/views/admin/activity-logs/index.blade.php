@extends('admin.layouts.app')

@section('title', 'Activity Logs')
@section('page_title', 'Activity Logs')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Activity Logs</span>
    </nav>
@endsection

@section('content')
    <div class="flex h-full flex-col gap-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input type="text" id="searchInput" placeholder="Search by user, email, action, module or detail"
                            class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                    </div>

                    <select id="categoryFilter"
                        class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] px-4 py-2.5 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                        <option value="all">All Categories</option>
                        <option value="auth">Auth Activity</option>
                        <option value="activity">System Activity</option>
                    </select>

                    <select id="actionFilter"
                        class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] px-4 py-2.5 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white">
                        @foreach ($actions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2">
                        <button type="button" id="searchBtn"
                            class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626]">
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
                    @can('ActivityLogs_ViewAll')
                        <a id="exportCsvBtn" href="{{ route('admin.activity-logs', ['is_export' => 1]) }}"
                            class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-4 py-2.5 text-sm font-medium text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]">
                            <i class="fa-solid fa-file-csv mr-2 text-[13px]"></i>
                            Export CSV
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-2xl border border-[#eee4ca] bg-white px-4 py-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Total Logs</p>
                <p id="totalLogsStat" class="mt-2 text-2xl font-bold text-slate-900">0</p>
            </div>
            <div class="rounded-2xl border border-[#eee4ca] bg-white px-4 py-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Auth Activity</p>
                <p id="authLogsStat" class="mt-2 text-2xl font-bold text-slate-900">0</p>
            </div>
            <div class="rounded-2xl border border-[#eee4ca] bg-white px-4 py-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">System Activity</p>
                <p id="systemLogsStat" class="mt-2 text-2xl font-bold text-slate-900">0</p>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[#eee4ca] bg-white shadow-sm">
            <div class="theme-table-scroll min-h-0 flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-[#f2ead4]">
                    <thead class="sticky top-0 z-10 bg-[#fffaf0]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Action</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Module</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Summary</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Time</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody" class="divide-y divide-[#f6f0df] bg-white">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">Loading activity logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="shrink-0 flex flex-col gap-3 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4 md:flex-row md:items-center md:justify-between">
                <div id="tableMeta" class="text-sm text-slate-500">Showing 0 to 0 of 0 results</div>
                <div id="paginationWrapper" class="flex flex-wrap items-center gap-2"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const endpoint = @json(route('admin.activity-logs'));
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const actionFilter = document.getElementById('actionFilter');
            const searchBtn = document.getElementById('searchBtn');
            const resetBtn = document.getElementById('resetBtn');
            const exportCsvBtn = document.getElementById('exportCsvBtn');
            const logsTableBody = document.getElementById('logsTableBody');
            const paginationWrapper = document.getElementById('paginationWrapper');
            const tableMeta = document.getElementById('tableMeta');
            const totalLogsStat = document.getElementById('totalLogsStat');
            const authLogsStat = document.getElementById('authLogsStat');
            const systemLogsStat = document.getElementById('systemLogsStat');
            let state = { search: '', category: 'all', action: 'all', page: 1, loading: false, requestId: 0 };

            function escapeHtml(value) {
                return value === null || value === undefined
                    ? ''
                    : String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }

            function badgeClass(category) {
                return category === 'auth'
                    ? 'bg-blue-50 text-blue-700 border-blue-200'
                    : 'bg-amber-50 text-amber-700 border-amber-200';
            }

            function detailPreview(details) {
                if (!details || !details.length) {
                    return '<p class="mt-1 text-xs text-slate-400">No additional details.</p>';
                }

                const preview = details.slice(0, 2);
                return `<div class="mt-2 flex flex-col gap-1">${preview.map(detail => `<p class="text-xs leading-5 text-slate-500">${escapeHtml(detail)}</p>`).join('')}</div>`;
            }

            function renderRows(items) {
                if (!items.length) {
                    logsTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">No activity logs found.</td></tr>`;
                    return;
                }

                logsTableBody.innerHTML = items.map(item => `
                    <tr class="align-top transition hover:bg-[#fffdf7]">
                        <td class="px-6 py-4">
                            ${item.permissions?.can_view ? `<a href="${item.show_url}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]" title="View"><i class="fa-solid fa-eye text-[13px]"></i></a>` : '<span class="text-xs text-slate-400">N/A</span>'}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] ${badgeClass(item.category)}">${escapeHtml(item.category)}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-[#f7edd0] px-2.5 py-1 text-xs font-medium text-[#8a6a1c]">${escapeHtml(item.action_label)}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-700 whitespace-nowrap">${escapeHtml(item.module_label)}</td>
                        <td class="px-6 py-4 min-w-[320px]">
                            <p class="text-sm font-semibold leading-6 text-slate-800">${escapeHtml(item.summary)}</p>
                            ${detailPreview(item.details)}
                        </td>
                        <td class="px-6 py-4">
                            <div class="min-w-[180px]">
                                <p class="text-sm font-semibold text-slate-800">${escapeHtml(item.performed_by)}</p>
                                <p class="mt-1 text-xs text-slate-500 break-all">${escapeHtml(item.performed_by_email || 'N/A')}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 whitespace-nowrap">${escapeHtml(item.created_at_human || '')}</td>
                    </tr>
                `).join('');
            }

            function renderPagination(pagination) {
                paginationWrapper.innerHTML = '';
                if (!pagination || pagination.last_page <= 1) return;

                const buttons = [];
                buttons.push(`<button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === 1 ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${pagination.current_page - 1}" ${pagination.current_page === 1 ? 'disabled' : ''}>Prev</button>`);

                for (let page = 1; page <= pagination.last_page; page++) {
                    if (page === 1 || page === pagination.last_page || (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)) {
                        buttons.push(`<button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${page === pagination.current_page ? 'border-[#c79a2b] bg-[#c79a2b] text-white' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${page}">${page}</button>`);
                    } else if (page === pagination.current_page - 2 || page === pagination.current_page + 2) {
                        buttons.push('<span class="px-1 text-slate-400">...</span>');
                    }
                }

                buttons.push(`<button type="button" class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === pagination.last_page ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}" data-page="${pagination.current_page + 1}" ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>Next</button>`);
                paginationWrapper.innerHTML = buttons.join('');
            }

            function renderMeta(pagination) {
                tableMeta.textContent = `Showing ${pagination?.from ?? 0} to ${pagination?.to ?? 0} of ${pagination?.total ?? 0} results`;
            }

            function renderStats(stats) {
                totalLogsStat.textContent = stats?.total ?? 0;
                authLogsStat.textContent = stats?.auth ?? 0;
                systemLogsStat.textContent = stats?.activity ?? 0;
            }

            function setLoading() {
                logsTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-slate-500"><div class="inline-flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin text-[#b49543]"></i><span>Loading activity logs...</span></div></td></tr>`;
            }

            function updateExportUrl() {
                if (!exportCsvBtn) return;

                const params = new URLSearchParams();
                if (state.search) params.set('search', state.search);
                if (state.category !== 'all') params.set('category', state.category);
                if (state.action !== 'all') params.set('action', state.action);
                params.set('is_export', '1');
                exportCsvBtn.href = `${endpoint}?${params.toString()}`;
            }

            async function fetchLogs() {
                if (state.loading) return;

                state.loading = true;
                state.requestId += 1;
                const currentRequestId = state.requestId;
                setLoading();
                updateExportUrl();

                const params = new URLSearchParams();
                if (state.search) params.set('search', state.search);
                if (state.category !== 'all') params.set('category', state.category);
                if (state.action !== 'all') params.set('action', state.action);
                if (state.page > 1) params.set('page', state.page);

                try {
                    const response = await fetch(`${endpoint}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (currentRequestId !== state.requestId) return;
                    if (!response.ok || !result.status) throw new Error(result.message || 'Failed to fetch activity logs.');

                    const items = result.data.items || [];
                    renderRows(items);
                    renderPagination(result.data.pagination || {});
                    renderMeta(result.data.pagination || {});
                    renderStats(result.data.stats || {});
                } catch (error) {
                    logsTableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">${escapeHtml(error.message || 'Something went wrong.')}</td></tr>`;
                    paginationWrapper.innerHTML = '';
                    renderMeta({ from: 0, to: 0, total: 0 });
                    renderStats({ total: 0, auth: 0, activity: 0 });
                } finally {
                    state.loading = false;
                }
            }

            const urlParams = new URLSearchParams(window.location.search);
            state.search = urlParams.get('search') || '';
            state.category = urlParams.get('category') || 'all';
            state.action = urlParams.get('action') || 'all';
            state.page = parseInt(urlParams.get('page') || '1', 10);

            searchInput.value = state.search;
            categoryFilter.value = state.category;
            actionFilter.value = state.action;

            searchBtn.addEventListener('click', function () {
                state.search = searchInput.value.trim();
                state.category = categoryFilter.value;
                state.action = actionFilter.value;
                state.page = 1;
                fetchLogs();
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchBtn.click();
                }
            });

            resetBtn.addEventListener('click', function () {
                state = { search: '', category: 'all', action: 'all', page: 1, loading: false, requestId: state.requestId };
                searchInput.value = '';
                categoryFilter.value = 'all';
                actionFilter.value = 'all';
                fetchLogs();
            });

            paginationWrapper.addEventListener('click', function (e) {
                const btn = e.target.closest('.page-btn');
                if (!btn || btn.disabled) return;

                const page = parseInt(btn.dataset.page || '1', 10);
                if (!page || page < 1 || page === state.page) return;

                state.page = page;
                fetchLogs();
            });

            fetchLogs();
        });
    </script>
@endpush
