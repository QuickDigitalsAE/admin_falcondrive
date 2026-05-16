@php
    $selectedPermissionNames = collect(old('permissions', isset($role) ? $role->permissions->pluck('name')->all() : []))->values()->all();
    $selectedRoleLevel = old('role_level', $role->role_level ?? 'admin');
@endphp

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="space-y-2">
        <label for="name" class="text-sm font-semibold text-slate-700">Role Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $role->name ?? '') }}"
            class="w-full rounded-[18px] border {{ $errors->has('name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
        @error('name')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="role_level" class="text-sm font-semibold text-slate-700">Role Level</label>
        <select id="role_level" name="role_level"
            class="w-full rounded-[18px] border {{ $errors->has('role_level') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
            @foreach ($roleLevels as $levelKey => $level)
                <option value="{{ $levelKey }}" {{ $selectedRoleLevel === $levelKey ? 'selected' : '' }}>
                    {{ $level['label'] }}
                </option>
            @endforeach
        </select>
        @error('role_level')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="overflow-hidden rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb]">
    <div class="border-b border-[#f0e6ca] px-5 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Permission Assignment</p>
                <h3 class="mt-1 text-xl font-bold text-slate-900">Allowed Permissions</h3>
                <p id="roleLevelDescription" class="mt-1 text-sm text-slate-500"></p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="permissionGroupSearch" type="text" placeholder="Search permission groups"
                        class="w-full rounded-xl border border-[#eadfbe] bg-white py-2 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5] sm:w-64">
                </div>
                <button type="button" id="toggleVisiblePermissions"
                    class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-4 py-2 text-sm font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]">
                    <i class="fa-solid fa-check-double mr-2 text-[12px]"></i>
                    <span>Select All</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @foreach ($permissionGroups as $groupName => $permissions)
            <div class="permission-group rounded-[18px] border border-[#eadfbe] bg-white p-3 shadow-sm"
                data-group-name="{{ strtolower($groupName) }}">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div>
                        <h4 class="text-[13px] font-semibold leading-tight text-slate-900">{{ $groupName }}</h4>
                        <p class="text-xs text-slate-500">{{ count($permissions) }} permission(s)</p>
                    </div>
                    <button type="button"
                        class="group-toggle-all inline-flex items-center gap-1.5 rounded-full border border-[#eadfbe] bg-[#fff5d8] px-2.5 py-1 text-[10px] font-semibold text-[#8a6a1c] transition hover:bg-[#ffefc2]">
                        <i class="fa-solid fa-check-double text-[9px]"></i>
                        <span>Select All</span>
                    </button>
                </div>

                <div class="space-y-2">
                    @foreach ($permissions as $permission)
                        @php
                            $checked = in_array($permission['name'], $selectedPermissionNames, true);
                        @endphp
                        <label class="permission-item flex cursor-pointer items-start gap-2 rounded-xl border border-[#f0e6ca] bg-[#fffdf8] px-3 py-2 transition hover:border-[#dcc57d] hover:bg-[#fff8e8]"
                            data-levels='@json($permission['allowed_levels'])'>
                            <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                                class="permission-checkbox mt-1 h-4 w-4 rounded border-[#d4bc78] text-[#c79a2b] focus:ring-[#d6ab3d]"
                                {{ $checked ? 'checked' : '' }}>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13px] font-semibold leading-tight text-slate-800">{{ $permission['name'] }}</span>
                                <span class="mt-0.5 block text-[11px] leading-tight text-slate-500">{{ $permission['table_name'] ?: 'No table mapping' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <button type="button"
                    class="permission-toggle mt-3 hidden w-full rounded-xl border border-[#eadfbe] bg-[#fffaf0] px-3 py-2 text-[13px] font-semibold text-[#7d6220] transition hover:bg-[#fff3d9]"
                    data-expand-label="View More"
                    data-collapse-label="Show Less">
                </button>
            </div>
        @endforeach
    </div>

    @error('permissions')
        <p class="px-5 pb-4 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
    @error('permissions.*')
        <p class="px-5 pb-4 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
