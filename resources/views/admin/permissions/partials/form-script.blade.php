<script>
    document.addEventListener('DOMContentLoaded', function () {
        const permissionInput = document.getElementById('name');
        const preview = document.getElementById('permissionNamePreview');
        const levelPreview = document.getElementById('permissionLevelPreview');
        const roleLevels = @json($roleLevels);

        function wildcardMatch(pattern, value) {
            if (pattern === '*') return true;
            const escaped = pattern.replace(/[.+^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
            return new RegExp(`^${escaped}$`).test(value);
        }

        function renderLevels() {
            const permissionName = permissionInput?.value?.trim() || 'Module_Action';
            preview.textContent = permissionName;

            const chips = [];

            Object.entries(roleLevels).forEach(([levelKey, config]) => {
                const matched = (config.patterns || []).some(pattern => wildcardMatch(pattern, permissionName));
                if (matched) {
                    chips.push(`<span class="inline-flex rounded-full bg-[#f7edd0] px-3 py-1 text-xs font-semibold text-[#8a6a1c]">${config.label}</span>`);
                }
            });

            levelPreview.innerHTML = chips.length
                ? chips.join('')
                : '<span class="text-sm text-slate-400">No configured role level currently matches this permission.</span>';
        }

        permissionInput?.addEventListener('input', renderLevels);
        renderLevels();
    });
</script>
