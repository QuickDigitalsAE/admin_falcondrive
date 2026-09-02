@extends('admin.layouts.app')

@section('title', 'Edit Customer Document')
@section('page_title', 'Edit Customer Document')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <a href="{{ route('admin.customer-documents') }}" class="transition hover:text-[#9b7a28]">Customers Documents</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="font-medium text-slate-700">Edit Document</span>
    </nav>
@endsection

@section('content')
    <section class="mx-auto w-full max-w-7xl pb-8">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-5 py-6 sm:px-7">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#b89a4c]">Customer Documents</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Edit Customer Document</h1>
                <p class="mt-1 text-sm text-slate-500">Update document details or replace the uploaded file.</p>
            </div>
            <form action="{{ route('admin.customer-documents.update', $document->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-5 sm:p-7">
                @csrf
                @method('PUT')
                @include('admin.customer-documents._form')
            </form>
        </div>
    </section>
@endsection
