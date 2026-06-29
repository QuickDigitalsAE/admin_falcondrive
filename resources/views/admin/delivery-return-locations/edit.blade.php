@extends('admin.layouts.app')

@section('title', 'Edit Location')
@section('page_title', 'Edit Location')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500"><a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><a href="{{ route('admin.delivery-return-locations') }}" class="transition hover:text-[#9b7a28]">Delivery & Return Locations</a><i class="fas fa-chevron-right text-[10px] text-slate-400"></i><span class="font-medium text-slate-700">Edit</span></nav>
@endsection

@section('content')
<section class="w-full pb-8">
    <div class="mx-auto w-full max-w-7xl">
        <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Location Management</p>
                        <h1 class="text-[28px] font-bold leading-tight text-slate-900">Edit Location</h1>
                        <p class="mt-1 text-sm text-slate-500">Update title, detail, pickup mapping, coordinates, price and location type.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.delivery-return-locations.show', $location->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Location</a>
                        <a href="{{ route('admin.delivery-return-locations') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
            </div>
            <form action="{{ route('admin.delivery-return-locations.update', $location->id) }}" method="POST" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                @method('PUT')
                @include('admin.delivery-return-locations._form', ['submitLabel' => 'Update Location'])
            </form>
        </div>
    </div>
</section>
@endsection
