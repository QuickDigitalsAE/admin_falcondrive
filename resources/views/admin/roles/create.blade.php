@extends('admin.layouts.app')

@section('title', 'Create Role')
@section('page_title', 'Create Role')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.roles') }}" class="transition hover:text-[#9b7a28]">Roles</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Role</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Roles Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Role</h1>
                            <p class="mt-1 text-sm text-slate-500">Create a new role, choose its level, and assign allowed permissions.</p>
                        </div>
                        <a href="{{ route('admin.roles') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>

                <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6 p-4 sm:p-6">
                    @csrf
                    @include('admin.roles._form')

                    <div class="rounded-[22px] border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-4 sm:px-5">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]"><i class="fa-solid fa-circle-info text-sm"></i></div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-slate-800">Before saving</h3>
                                <p class="mt-1 text-sm text-slate-500">Make sure the role level matches the business hierarchy and only the intended permissions remain selected.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>Save Role</button>
                        <a href="{{ route('admin.roles') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-xmark mr-2 text-[13px]"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    @include('admin.roles.partials.form-script')
@endpush
