@extends('admin.layouts.app')

@section('title', 'Customer Document Details')
@section('page_title', 'Customer Document Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <a href="{{ route('admin.customer-documents') }}" class="transition hover:text-[#9b7a28]">Customers Documents</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="font-medium text-slate-700">Details</span>
    </nav>
@endsection

@section('content')
    @php
        $documentUrl = $document->path
        ? asset('storage/' . $document->path)
        : ($document->document && str_starts_with($document->document, 'data:')
            ? $document->document
            : ($document->document && $document->data
                ? 'data:' . $document->data . ';base64,' . $document->document
                : ($document->document ? asset('storage/' . $document->document) : null)));
    @endphp

    <section class="mx-auto w-full max-w-5xl pb-8">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-5 py-6">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-[#b89a4c]">Customer Documents</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $document->identity_name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ trim($document->customer?->first_name . ' ' . $document->customer?->last_name) ?: 'Unknown customer' }}
                    </p>
                </div>

                <div class="flex gap-2">
                    @if (!$document->trashed())
                        @can('CustomerDocument_Edit')
                            <a href="{{ route('admin.customer-documents.edit', $document->id) }}" class="inline-flex items-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-semibold text-white">
                                <i class="fa-solid fa-pen-to-square mr-2"></i>
                                Edit
                            </a>
                        @endcan
                    @endif
                    <a href="{{ route('admin.customer-documents') }}" class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-4 py-2.5 text-sm font-semibold text-[#7d6220]">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                @foreach ([
                    ['Document Number', $document->document_no],
                    ['Document Type ID', $document->identity_document_id],
                    ['Issue Date', $document->issue_date],
                    ['Expiry Date', $document->expiry_date],
                    ['Issued By', $document->issued_by],
                    ['Data / MIME Type', $document->data],
                ] as [$label, $value])
                    <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#b89a4c]">{{ $label }}</p>
                        <p class="mt-2 text-sm text-slate-900">{{ $value ?: 'N/A' }}</p>
                    </div>
                @endforeach

                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#b89a4c]">Status</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($document->status) }}</p>
                </div>

                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#b89a4c]">Customer ID</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $document->customer_id }}</p>
                </div>

                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4 sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#b89a4c]">Description</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $document->description ?: 'No description added.' }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#eadfbe] bg-[#fffaf0] p-4 sm:col-span-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#b89a4c]">Uploaded File</p>
                        <p class="mt-2 text-sm text-slate-700">{{ $document->file_name_without_extension ?: $document->file_name ?: 'No file' }}</p>
                    </div>
                    @if ($documentUrl)
                        <a target="_blank" href="{{ $documentUrl }}" class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-semibold text-white">
                            <i class="fa-solid fa-arrow-up-right-from-square mr-2"></i>
                            Open File
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
