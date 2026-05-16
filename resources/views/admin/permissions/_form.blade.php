<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="space-y-2">
        <label for="name" class="text-sm font-semibold text-slate-700">Permission Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $permission->name ?? '') }}" placeholder="User_ViewAll"
            class="w-full rounded-[18px] border {{ $errors->has('name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
        <p class="text-xs text-slate-500">Use the `Module_Action` format. Example: `Permissions_Edit`.</p>
        @error('name')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="table_name" class="text-sm font-semibold text-slate-700">Table Name</label>
        @if(isset($tableOptions) && is_array($tableOptions) && count($tableOptions))
            <select id="table_name" name="table_name"
                class="w-full rounded-[18px] border {{ $errors->has('table_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                <option value="">--select table name--</option>
                @foreach($tableOptions as $tableName)
                    <option value="{{ $tableName }}" {{ old('table_name', $permission->table_name ?? '') === $tableName ? 'selected' : '' }}>
                        {{ $tableName }}
                    </option>
                @endforeach
            </select>
        @else
            <input id="table_name" type="text" name="table_name" value="{{ old('table_name', $permission->table_name ?? '') }}" placeholder="permissions"
                class="w-full rounded-[18px] border {{ $errors->has('table_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
        @endif
        @error('table_name')
            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
    <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Format Guide</p>
        <h3 class="mt-1 text-xl font-bold text-slate-900">Naming Standard</h3>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-[#eadfbe] bg-white p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Module</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">User, Role, Permissions</p>
            </div>
            <div class="rounded-2xl border border-[#eadfbe] bg-white p-4">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Action</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">ViewAll, View, Add, Edit, Delete</p>
            </div>
        </div>
        <div class="mt-4 rounded-2xl border border-dashed border-[#eadfbe] bg-white px-4 py-4">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Preview</p>
            <p id="permissionNamePreview" class="mt-2 text-sm font-semibold text-slate-900">{{ old('name', $permission->name ?? 'Module_Action') }}</p>
        </div>
    </div>

    <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Role Level Visibility</p>
        <h3 class="mt-1 text-xl font-bold text-slate-900">Static Mapping Check</h3>
        <p class="mt-1 text-sm text-slate-500">Preview which configured role levels can access this permission name.</p>
        <div id="permissionLevelPreview" class="mt-4 flex flex-wrap gap-2"></div>
    </div>
</div>
