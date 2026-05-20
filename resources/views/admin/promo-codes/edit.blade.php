@extends('admin.layouts.app')

@section('title', 'Edit Promo Code')
@section('page_title', 'Edit Promo Code')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.promo-codes') }}" class="transition hover:text-[#9b7a28]">Promo Codes</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Edit</span>
    </nav>
@endsection

@section('content')
<div class="rounded-[28px] border border-[#eee4ca] bg-white/95 p-5 shadow-sm">
    <form method="POST" action="{{ route('admin.promo-codes.update', $promoCode->id) }}" class="space-y-6">
        @method('PUT')
        @include('admin.promo-codes._form', ['submitLabel' => 'Update Promo Code'])
    </form>
</div>
@endsection
