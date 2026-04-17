<script>
    document.addEventListener('DOMContentLoaded', function () {
        const collapsedPermissionLimit = 3;
        const roleLevelSelect = document.getElementById('role_level');
        const descriptionElement = document.getElementById('roleLevelDescription');
        const toggleVisibleButton = document.getElementById('toggleVisiblePermissions');
        const permissionGroupSearch = document.getElementById('permissionGroupSearch');
        const permissionItems = Array.from(document.querySelectorAll('.permission-item'));
        const roleLevels = @json($roleLevels);

        function getVisiblePermissionItems(group = null, respectGroupVisibility = true) {
            const items = group
                ? Array.from(group.querySelectorAll('.permission-item'))
                : permissionItems;

            return items.filter(item => {
                const parentGroup = item.closest('.permission-group');
                const isGroupVisible = !respectGroupVisibility || (parentGroup && !parentGroup.classList.contains('hidden'));

                return !item.classList.contains('hidden')
                    && parentGroup
                    && isGroupVisible;
            });
        }

        function setLevelCards(level) {
            document.querySelectorAll('[data-level-card]').forEach(card => {
                const active = card.dataset.levelCard === level;
                card.classList.toggle('border-[#d6ab3d]', active);
                card.classList.toggle('bg-[#fff8e7]', active);
                card.classList.toggle('border-[#eadfbe]', !active);
                card.classList.toggle('bg-[#fffdf8]', !active);
            });
        }

        function updateGroupVisibility(group) {
            const visibleItems = getVisiblePermissionItems(group, false);
            const toggleButton = group.querySelector('.permission-toggle');
            const groupToggleButton = group.querySelector('.group-toggle-all');
            const searchValue = (permissionGroupSearch?.value || '').trim().toLowerCase();
            const matchesSearch = searchValue === '' || (group.dataset.groupName || '').includes(searchValue);

            group.classList.toggle('hidden', visibleItems.length === 0 || !matchesSearch);

            if (!matchesSearch || visibleItems.length === 0) {
                if (toggleButton) {
                    toggleButton.classList.add('hidden');
                    toggleButton.dataset.expanded = 'false';
                    toggleButton.textContent = '';
                }

                if (groupToggleButton) {
                    const icon = groupToggleButton.querySelector('i');
                    const label = groupToggleButton.querySelector('span');

                    icon?.classList.remove('fa-eraser');
                    icon?.classList.add('fa-check-double');
                    if (label) label.textContent = 'Select All';
                }

                return;
            }

            if (groupToggleButton) {
                const allSelected = visibleItems.length > 0 && visibleItems.every(item => {
                    const input = item.querySelector('.permission-checkbox');
                    return !!input && input.checked;
                });

                const icon = groupToggleButton.querySelector('i');
                const label = groupToggleButton.querySelector('span');

                if (allSelected) {
                    icon?.classList.remove('fa-check-double');
                    icon?.classList.add('fa-eraser');
                    if (label) label.textContent = 'Clear';
                } else {
                    icon?.classList.remove('fa-eraser');
                    icon?.classList.add('fa-check-double');
                    if (label) label.textContent = 'Select All';
                }
            }

            if (!toggleButton) {
                return;
            }

            const isExpanded = toggleButton.dataset.expanded === 'true';
            const hiddenCount = Math.max(visibleItems.length - collapsedPermissionLimit, 0);

            visibleItems.forEach((item, index) => {
                const isOverflowHidden = !isExpanded && index >= collapsedPermissionLimit;
                item.dataset.overflowHidden = isOverflowHidden ? 'true' : 'false';
                item.style.display = isOverflowHidden ? 'none' : '';
            });

            if (hiddenCount > 0) {
                toggleButton.classList.remove('hidden');
                toggleButton.textContent = isExpanded
                    ? toggleButton.dataset.collapseLabel
                    : `${toggleButton.dataset.expandLabel} (${hiddenCount})`;
            } else {
                toggleButton.classList.add('hidden');
                toggleButton.dataset.expanded = 'false';
                toggleButton.textContent = '';
            }
        }

        function refreshGroupVisibility() {
            document.querySelectorAll('.permission-group').forEach(updateGroupVisibility);
            updateGlobalToggleButton();
        }

        function refreshPermissions() {
            const selectedLevel = roleLevelSelect?.value || 'admin';
            if (descriptionElement) {
                descriptionElement.textContent = roleLevels[selectedLevel]?.description || '';
            }

            setLevelCards(selectedLevel);

            permissionItems.forEach(item => {
                const levels = JSON.parse(item.dataset.levels || '[]');
                const input = item.querySelector('.permission-checkbox');
                const visible = levels.includes(selectedLevel);

                item.classList.toggle('hidden', !visible);

                if (!visible && input) {
                    input.checked = false;
                }
            });

            refreshGroupVisibility();
        }

        function updateGlobalToggleButton() {
            if (!toggleVisibleButton) {
                return;
            }

            const visibleItems = getVisiblePermissionItems();
            const allSelected = visibleItems.length > 0 && visibleItems.every(item => {
                const input = item.querySelector('.permission-checkbox');
                return !!input && input.checked;
            });

            const icon = toggleVisibleButton.querySelector('i');
            const label = toggleVisibleButton.querySelector('span');

            if (allSelected) {
                icon?.classList.remove('fa-check-double');
                icon?.classList.add('fa-eraser');
                if (label) label.textContent = 'Clear All';
            } else {
                icon?.classList.remove('fa-eraser');
                icon?.classList.add('fa-check-double');
                if (label) label.textContent = 'Select All';
            }
        }

        roleLevelSelect?.addEventListener('change', refreshPermissions);
        permissionGroupSearch?.addEventListener('input', refreshGroupVisibility);

        document.querySelectorAll('.permission-toggle').forEach(button => {
            button.dataset.expanded = 'false';
            button.addEventListener('click', function () {
                this.dataset.expanded = this.dataset.expanded === 'true' ? 'false' : 'true';
                const group = this.closest('.permission-group');
                if (group) {
                    updateGroupVisibility(group);
                }
            });
        });

        document.querySelectorAll('.group-toggle-all').forEach(button => {
            button.addEventListener('click', function () {
                const group = this.closest('.permission-group');
                if (!group) {
                    return;
                }

                const visibleItems = getVisiblePermissionItems(group);
                const allSelected = visibleItems.length > 0 && visibleItems.every(item => {
                    const input = item.querySelector('.permission-checkbox');
                    return !!input && input.checked;
                });

                visibleItems.forEach(item => {
                    if (item.classList.contains('hidden')) {
                        return;
                    }

                    const input = item.querySelector('.permission-checkbox');
                    if (input) {
                        input.checked = !allSelected;
                    }
                });

                updateGroupVisibility(group);
                updateGlobalToggleButton();
            });
        });

        toggleVisibleButton?.addEventListener('click', function () {
            const visibleItems = getVisiblePermissionItems();
            const allSelected = visibleItems.length > 0 && visibleItems.every(item => {
                const input = item.querySelector('.permission-checkbox');
                return !!input && input.checked;
            });

            visibleItems.forEach(item => {
                const input = item.querySelector('.permission-checkbox');
                if (input) {
                    input.checked = !allSelected;
                }
            });

            refreshGroupVisibility();
        });

        document.querySelectorAll('.permission-checkbox').forEach(input => {
            input.addEventListener('change', function () {
                refreshGroupVisibility();
            });
        });

        refreshPermissions();
    });
</script>
