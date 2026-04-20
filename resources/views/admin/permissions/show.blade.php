@extends('admin.layouts.app')

@section('title', 'Permission Details')
@section('page_title', 'Permission Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.permissions') }}" class="transition hover:text-[#9b7a28]">Permissions</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Permission Details</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Permissions Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Permission Profile</h1>
                            <p class="mt-1 text-sm text-slate-500">Detailed view of this permission and its configured role-level availability.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                        @can('Permissions_Edit')
                            @if (is_null($permission->deleted_at))
                                <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Permission</a>
                            @endif
                        @endcan
                            <a href="{{ route('admin.permissions') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                        <div class="xl:col-span-1">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                                <div class="flex flex-col items-center text-center">
                                    <div class="flex h-28 w-28 items-center justify-center rounded-3xl border border-[#eadfbe] bg-white shadow-sm ring-4 ring-[#fbf2d6]"><i class="fa-solid fa-key text-3xl text-[#b49543]"></i></div>
                                    <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $permission->name }}</h2>
                                    <p class="mt-2 inline-flex rounded-full bg-[#f8edd0] px-3 py-1 text-xs font-semibold text-[#8b6717] ring-1 ring-[#ecdca8]">{{ \Illuminate\Support\Str::before($permission->name, '_') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="xl:col-span-2">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <div class="mb-5">
                                    <h3 class="text-lg font-semibold text-slate-900">Permission Information</h3>
                                    <p class="mt-1 text-sm text-slate-500">System naming and assignment details.</p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Permission Name</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $permission->name }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Table Name</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $permission->table_name ?: 'N/A' }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Assigned Roles</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ $permission->roles->count() }}</p></div>
                                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Created At</p><p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($permission->created_at)->format('d M Y, h:i A') ?: 'N/A' }}</p></div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <h3 class="text-lg font-semibold text-slate-900">Configured Role Levels</h3>
                                <p class="mt-1 text-sm text-slate-500">These levels currently match the static mapping for this permission name.</p>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @forelse ($allowedLevels as $level)
                                        <span class="inline-flex rounded-full bg-[#f7edd0] px-3 py-1 text-xs font-semibold text-[#8a6a1c]">{{ \App\Support\RolePermissionMatrix::label($level) }}</span>
                                    @empty
                                        <span class="text-sm text-slate-400">No configured role level currently matches this permission.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
                        @can('Permissions_Edit')
                            @if (is_null($permission->deleted_at))
                                <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Permission</a>
                            @endif
                        @endcan
                        <a href="{{ route('admin.permissions') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-list mr-2 text-[13px]"></i>All Permissions</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
