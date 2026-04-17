@extends('admin.layouts.app')

@section('title', 'Create Setting')
@section('page_title', 'Create Setting')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.settings') }}" class="transition hover:text-[#9b7a28]">Settings</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Setting</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Website Settings</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Setting</h1>
                            <p class="mt-1 text-sm text-slate-500">Add website configuration keys with readable labels, grouped organization, optional metadata, and type-aware value handling.</p>
                        </div>
                        <a href="{{ route('admin.settings') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
                <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @include('admin.settings._form', ['setting' => null, 'submitLabel' => 'Save Setting'])
                </form>
            </div>
        </div>
    </section>
@endsection

@include('admin.layouts.partials.resource-ckeditor')
