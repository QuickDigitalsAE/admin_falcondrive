{{-- Sidebar / Collapse / Modal / Toast Scripts --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const collapseButtons = document.querySelectorAll('.sidebar-collapse-btn');

        const profileDropdownWrapper = document.getElementById('profileDropdownWrapper');
        const profileDropdownToggle = document.getElementById('profileDropdownToggle');
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');
        const profileDropdownChevron = document.getElementById('profileDropdownChevron');

        if (profileDropdownToggle && profileDropdownMenu) {
            profileDropdownToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');

                if (profileDropdownChevron) {
                    profileDropdownChevron.classList.toggle('rotate-180');
                }
            });

            document.addEventListener('click', function (e) {
                if (!profileDropdownWrapper.contains(e.target)) {
                    profileDropdownMenu.classList.add('hidden');

                    if (profileDropdownChevron) {
                        profileDropdownChevron.classList.remove('rotate-180');
                    }
                }
            });
        }

        function isDesktop() {
            return window.innerWidth >= 1024;
        }

        function closeAllSubmenus(exceptId = null) {
            const allMenus = document.querySelectorAll('.sidebar-submenu');
            const allButtons = document.querySelectorAll('.sidebar-collapse-btn');
            const allArrows = document.querySelectorAll('.sidebar-arrow');

            allMenus.forEach(menu => {
                if (!exceptId || menu.id !== exceptId) {
                    menu.classList.add('hidden');
                }
            });

            allButtons.forEach(button => {
                const targetId = button.getAttribute('data-target');
                if (!exceptId || targetId !== exceptId) {
                    button.setAttribute('aria-expanded', 'false');
                }
            });

            allArrows.forEach(arrow => {
                const arrowTarget = arrow.getAttribute('data-arrow');
                if (!exceptId || arrowTarget !== exceptId) {
                    arrow.classList.remove('rotate-180');
                }
            });
        }

        function openSubmenu(targetId, button) {
            const targetMenu = document.getElementById(targetId);
            const arrow = document.querySelector(`[data-arrow="${targetId}"]`);

            if (!targetMenu) return;

            closeAllSubmenus(targetId);

            targetMenu.classList.remove('hidden');
            button.setAttribute('aria-expanded', 'true');

            if (arrow) {
                arrow.classList.add('rotate-180');
            }
        }

        function closeSubmenu(targetId, button) {
            const targetMenu = document.getElementById(targetId);
            const arrow = document.querySelector(`[data-arrow="${targetId}"]`);

            if (!targetMenu) return;

            targetMenu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');

            if (arrow) {
                arrow.classList.remove('rotate-180');
            }
        }

        function toggleSidebar() {
            if (!sidebar) return;

            if (isDesktop()) {
                sidebar.classList.toggle('sidebar-desktop-collapsed');

                // desktop collapse ke waqt saare submenu close kar do
                if (sidebar.classList.contains('sidebar-desktop-collapsed')) {
                    closeAllSubmenus();
                }
            } else {
                sidebar.classList.toggle('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.toggle('hidden');
            }
        }

        function closeMobileSidebar() {
            if (!sidebar || isDesktop()) return;
            sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }

        function handleResize() {
            if (!sidebar) return;

            if (isDesktop()) {
                sidebar.classList.remove('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('sidebar-desktop-collapsed');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }

        window.addEventListener('resize', handleResize);
        handleResize();

        const toggleButtons = document.querySelectorAll('.sidebar-collapse-btn');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('data-target');
                const targetMenu = document.getElementById(targetId);

                if (!targetId || !targetMenu) return;

                // agar desktop collapsed mode me hai to pehle sidebar expand ho
                if (isDesktop() && sidebar.classList.contains('sidebar-desktop-collapsed')) {
                    sidebar.classList.remove('sidebar-desktop-collapsed');

                    setTimeout(() => {
                        openSubmenu(targetId, this);
                    }, 180);

                    return;
                }

                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const isHidden = targetMenu.classList.contains('hidden');

                if (isHidden || !isExpanded) {
                    openSubmenu(targetId, this);
                } else {
                    closeSubmenu(targetId, this);
                }
            });
        });

        // Toast container dynamically
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-[9999] space-y-3 w-[calc(100%-2rem)] max-w-sm';
            document.body.appendChild(container);
        }

        // Expose globally
        window.openDeleteModal = function (actionUrl) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');

            if (form) {
                form.action = actionUrl;
            }

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
            toast.className = `
                flex items-start gap-3 text-white px-5 py-4 rounded-2xl shadow-2xl
                transform translate-x-full opacity-0 transition-all duration-500
                ${bgColor}
            `;

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
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 500);
        };

        // ESC closes delete modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

        // Session toasts
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