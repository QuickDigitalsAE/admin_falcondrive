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

        const DESKTOP_BREAKPOINT = 1000;
        const SIDEBAR_STORAGE_KEY = 'admin_sidebar_desktop_collapsed';

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
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                closeProfileDropdown();
                closeNotificationDropdown();
                closeMobileSearch();
                closeMobileSidebar();
            }
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