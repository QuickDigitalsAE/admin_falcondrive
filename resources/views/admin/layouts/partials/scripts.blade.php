<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        const profileDropdownWrapper = document.getElementById('profileDropdownWrapper');
        const profileDropdownToggle = document.getElementById('profileDropdownToggle');
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');
        const profileDropdownChevron = document.getElementById('profileDropdownChevron');

        const notificationDropdownWrapper = document.getElementById('notificationDropdownWrapper');
        const notificationDropdownToggle = document.getElementById('notificationDropdownToggle');
        const notificationDropdownMenu = document.getElementById('notificationDropdownMenu');

        const mobileSearchToggle = document.getElementById('mobileSearchToggle');
        const mobileSearchPanel = document.getElementById('mobileSearchPanel');
        const mobileSearchInput = document.getElementById('mobileSearchInput');
        const globalSearchInput = document.getElementById('globalSearchInput');
        const globalSearchDropdown = document.getElementById('globalSearchDropdown');
        const globalSearchDesktopWrapper = document.getElementById('globalSearchDesktopWrapper');
        const globalSearchMobileWrapper = document.getElementById('globalSearchMobileWrapper');
        const globalSearchMobileDropdown = document.getElementById('globalSearchMobileDropdown');

        const DESKTOP_BREAKPOINT = 1000;
        const SIDEBAR_STORAGE_KEY = 'admin_sidebar_desktop_collapsed';
        let globalSearchTimer = null;
        let globalSearchRequestId = 0;

        function isDesktop() {
            return window.innerWidth >= DESKTOP_BREAKPOINT;
        }

        function setBodyScrollLock(locked) {
            if (locked) {
                document.body.classList.add('overflow-hidden');
                document.documentElement.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
                document.documentElement.classList.remove('overflow-hidden');
            }
        }

        function closeProfileDropdown() {
            if (profileDropdownMenu) profileDropdownMenu.classList.add('hidden');
            if (profileDropdownChevron) profileDropdownChevron.classList.remove('rotate-180');
        }

        function closeNotificationDropdown() {
            if (notificationDropdownMenu) notificationDropdownMenu.classList.add('hidden');
        }

        function closeMobileSearch() {
            if (mobileSearchPanel) mobileSearchPanel.classList.add('hidden');
            closeGlobalSearchDropdowns();
        }

        function closeGlobalSearchDropdowns() {
            if (globalSearchDropdown) globalSearchDropdown.classList.add('hidden');
            if (globalSearchMobileDropdown) globalSearchMobileDropdown.classList.add('hidden');
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderSearchDropdown(target, content) {
            if (!target) {
                return;
            }

            target.innerHTML = content;
            target.classList.remove('hidden');
        }

        function renderSearchLoading(target) {
            renderSearchDropdown(target, `
                <div class="px-4 py-6">
                    <div class="flex items-center gap-3 text-sm text-slate-500">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#fff1c8] text-[#b49543]">
                            <i class="fas fa-spinner fa-spin text-[13px]"></i>
                        </span>
                        <div>
                            <p class="font-semibold text-slate-800">Searching modules</p>
                            <p class="text-xs text-slate-500">Fetching permitted matches...</p>
                        </div>
                    </div>
                </div>
            `);
        }

        function renderSearchResults(target, payload, query) {
            if (!target) {
                return;
            }

            const results = payload?.data || { groups: [], quick_links: [], total_results: 0 };
            const groups = Array.isArray(results.groups) ? results.groups : [];
            const quickLinks = Array.isArray(results.quick_links) ? results.quick_links : [];
            const searchPage = globalSearchInput?.dataset.searchPage || mobileSearchInput?.dataset.searchPage || '#';
            const encodedQuery = encodeURIComponent(query);

            if (!query.trim()) {
                target.classList.add('hidden');
                target.innerHTML = '';
                return;
            }

            if (groups.length === 0 && quickLinks.length === 0) {
                renderSearchDropdown(target, `
                    <div class="px-4 py-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff1c8] text-[#b49543]">
                            <i class="fas fa-folder-open text-[14px]"></i>
                        </div>
                        <p class="mt-4 text-sm font-semibold text-slate-900">No matching results</p>
                        <p class="mt-1 text-xs text-slate-500">Try a different keyword.</p>
                    </div>
                `);

                return;
            }

            const quickLinksHtml = quickLinks.length
                ? `
                    <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Quick Access</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            ${quickLinks.map(link => `
                                <a href="${escapeHtml(link.url)}"
                                    class="inline-flex items-center gap-2 rounded-full border border-[#eadfbe] bg-white px-3 py-1.5 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff3d9]">
                                    <i class="fas ${escapeHtml(link.icon)} text-[11px]"></i>
                                    <span>${escapeHtml(link.label)}</span>
                                </a>
                            `).join('')}
                        </div>
                    </div>
                `
                : '';

            const groupsHtml = groups.map(group => `
                <div class="border-b border-[#f5eedb] last:border-b-0">
                    <div class="flex items-center justify-between px-4 pb-2 pt-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#fff1c8] text-[#9b7a28]">
                                <i class="fas ${escapeHtml(group.icon)} text-[11px]"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">${escapeHtml(group.label)}</p>
                                <p class="text-[11px] text-slate-500">${group.items.length} result(s)</p>
                            </div>
                        </div>
                        <a href="${escapeHtml(group.index_url)}" class="text-[11px] font-semibold text-[#a67d20] transition hover:opacity-80">Open</a>
                    </div>
                    <div class="space-y-2 px-4 pb-4">
                        ${group.items.map(item => `
                            <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] px-3 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-900">${escapeHtml(item.title)}</p>
                                        ${item.subtitle ? `<p class="mt-1 text-xs text-slate-600">${escapeHtml(item.subtitle)}</p>` : ''}
                                        ${item.meta ? `<p class="mt-1 text-[11px] text-slate-500">${escapeHtml(item.meta)}</p>` : ''}
                                    </div>
                                    <span class="shrink-0 rounded-full bg-[#f7edd0] px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#8a6a1c]">${escapeHtml(item.badge)}</span>
                                </div>
                                ${item.actions?.length ? `
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        ${item.actions.map(action => `
                                            <a href="${escapeHtml(action.url)}"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-[#eadfbe] bg-white px-3 py-1.5 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff3d9]">
                                                <i class="fas ${escapeHtml(action.icon)} text-[10px]"></i>
                                                <span>${escapeHtml(action.label)}</span>
                                            </a>
                                        `).join('')}
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');

            renderSearchDropdown(target, `
                <div class="max-h-[70vh] overflow-y-auto">
                    ${quickLinksHtml}
                    ${groupsHtml}
                </div>
                <div class="border-t border-[#f0e6ca] bg-[#fffaf0] px-4 py-3 text-center">
                    <a href="${escapeHtml(`${searchPage}?q=${encodedQuery}`)}"
                        class="inline-flex items-center text-sm font-semibold text-[#9b7a28] transition hover:opacity-80">
                        View all results
                    </a>
                </div>
            `);
        }

        function handleGlobalSearch(input, dropdown) {
            if (!input || !dropdown) {
                return;
            }

            const query = input.value.trim();
            const endpoint = input.dataset.searchEndpoint;

            clearTimeout(globalSearchTimer);

            if (!query || !endpoint) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                return;
            }

            globalSearchTimer = setTimeout(async function () {
                const requestId = ++globalSearchRequestId;
                renderSearchLoading(dropdown);

                try {
                    const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    const payload = await response.json();

                    if (requestId !== globalSearchRequestId) {
                        return;
                    }

                    renderSearchResults(dropdown, payload, query);
                } catch (error) {
                    if (requestId !== globalSearchRequestId) {
                        return;
                    }

                    renderSearchDropdown(dropdown, `
                        <div class="px-4 py-8 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500">
                                <i class="fas fa-triangle-exclamation text-[14px]"></i>
                            </div>
                            <p class="mt-4 text-sm font-semibold text-slate-900">Search unavailable</p>
                            <p class="mt-1 text-xs text-slate-500">Please try again in a moment.</p>
                        </div>
                    `);
                }
            }, 220);
        }

        function openMobileSidebar() {
            if (!sidebar || isDesktop()) return;

            sidebar.classList.add('sidebar-open');
            sidebar.classList.remove('-translate-x-full');

            if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');

            setBodyScrollLock(true);
        }

        function closeMobileSidebar() {
            if (!sidebar || isDesktop()) return;

            sidebar.classList.remove('sidebar-open');
            sidebar.classList.add('-translate-x-full');

            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');

            setBodyScrollLock(false);
        }

        function setDesktopCollapsedState(collapsed) {
            if (!sidebar) return;

            if (collapsed) {
                sidebar.classList.add('sidebar-desktop-collapsed');
            } else {
                sidebar.classList.remove('sidebar-desktop-collapsed');
            }

            localStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? '1' : '0');
        }

        function getDesktopCollapsedState() {
            return localStorage.getItem(SIDEBAR_STORAGE_KEY) === '1';
        }

        function toggleSidebar() {
            if (!sidebar) return;

            if (isDesktop()) {
                const isCollapsed = sidebar.classList.contains('sidebar-desktop-collapsed');
                setDesktopCollapsedState(!isCollapsed);
            } else {
                const isOpen = sidebar.classList.contains('sidebar-open');
                if (isOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            }
        }

        function handleResize() {
            if (!sidebar) return;

            if (isDesktop()) {
                sidebar.classList.remove('sidebar-open');
                sidebar.classList.remove('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
                setBodyScrollLock(false);

                const desktopCollapsed = getDesktopCollapsedState();
                setDesktopCollapsedState(desktopCollapsed);
            } else {
                sidebar.classList.remove('sidebar-desktop-collapsed');
                sidebar.classList.remove('sidebar-open');
                sidebar.classList.add('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
                setBodyScrollLock(false);
            }
        }

        if (notificationDropdownToggle && notificationDropdownMenu) {
            notificationDropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                notificationDropdownMenu.classList.toggle('hidden');
                closeProfileDropdown();
                closeMobileSearch();
            });
        }

        if (profileDropdownToggle && profileDropdownMenu) {
            profileDropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');
                closeNotificationDropdown();
                closeMobileSearch();

                if (profileDropdownChevron) {
                    profileDropdownChevron.classList.toggle('rotate-180');
                }
            });
        }

        if (mobileSearchToggle && mobileSearchPanel) {
            mobileSearchToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                mobileSearchPanel.classList.toggle('hidden');
                closeProfileDropdown();
                closeNotificationDropdown();

                if (!mobileSearchPanel.classList.contains('hidden') && mobileSearchInput) {
                    setTimeout(() => mobileSearchInput.focus(), 50);
                }
            });
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeProfileDropdown();
                closeNotificationDropdown();
                closeMobileSearch();
                toggleSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }

        document.addEventListener('click', function (e) {
            if (profileDropdownWrapper && !profileDropdownWrapper.contains(e.target)) {
                closeProfileDropdown();
            }

            if (notificationDropdownWrapper && !notificationDropdownWrapper.contains(e.target)) {
                closeNotificationDropdown();
            }

            if (
                mobileSearchPanel &&
                mobileSearchToggle &&
                !mobileSearchPanel.contains(e.target) &&
                !mobileSearchToggle.contains(e.target)
            ) {
                closeMobileSearch();
            }

            if (globalSearchDesktopWrapper && !globalSearchDesktopWrapper.contains(e.target)) {
                if (globalSearchDropdown) {
                    globalSearchDropdown.classList.add('hidden');
                }
            }

            if (globalSearchMobileWrapper && !globalSearchMobileWrapper.contains(e.target)) {
                if (globalSearchMobileDropdown) {
                    globalSearchMobileDropdown.classList.add('hidden');
                }
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeProfileDropdown();
                closeNotificationDropdown();
                closeMobileSearch();
                closeMobileSidebar();
                closeGlobalSearchDropdowns();
            }
        });

        [globalSearchInput, mobileSearchInput].forEach(input => {
            if (!input) {
                return;
            }

            const isDesktopInput = input === globalSearchInput;
            const dropdown = isDesktopInput ? globalSearchDropdown : globalSearchMobileDropdown;

            input.addEventListener('input', function () {
                handleGlobalSearch(input, dropdown);
            });

            input.addEventListener('focus', function () {
                handleGlobalSearch(input, dropdown);
            });
        });

        if (sidebar) {
            sidebar.addEventListener('click', function (e) {
                const clickedLink = e.target.closest('a, button[type="submit"]');
                if (!clickedLink) return;

                if (!isDesktop()) {
                    closeMobileSidebar();
                }
            });
        }

        window.addEventListener('resize', handleResize);
        window.addEventListener('orientationchange', handleResize);
        handleResize();

        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-[9999] space-y-3 w-[calc(100%-2rem)] max-w-sm';
            document.body.appendChild(container);
        }

        window.openDeleteModal = function (actionUrl) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');

            if (form) form.action = actionUrl;

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        };

        window.closeDeleteModal = function () {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        window.showToast = function (message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            let bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

            const toast = document.createElement('div');
            toast.className = `flex items-start gap-3 text-white px-5 py-4 rounded-2xl shadow-2xl transform translate-x-full opacity-0 transition-all duration-500 ${bgColor}`;

            toast.innerHTML = `
                <div class="flex-1 text-sm leading-snug break-words">${message}</div>
                <button type="button" class="font-bold text-lg leading-none opacity-80 hover:opacity-100">&times;</button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);

            const closeBtn = toast.querySelector('button');
            if (closeBtn) {
                closeBtn.onclick = () => removeToast(toast);
            }

            setTimeout(() => removeToast(toast), 3000);
        };

        window.removeToast = function (toast) {
            if (!toast) return;

            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 500);
        };

        @if(session('success'))
            showToast(@json(session('success')), 'success');
        @endif

        @if(session('error'))
            showToast(@json(session('error')), 'error');
        @endif

        @if($errors->any())
            showToast(@json($errors->first()), 'error');
        @endif
    });
</script>
