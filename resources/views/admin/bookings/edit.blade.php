@extends('admin.layouts.app')

@section('title', 'Edit Booking')
@section('page_title', 'Edit Booking')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.bookings') }}" class="transition hover:text-[#9b7a28]">Bookings</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Edit Booking</span>
    </nav>
@endsection

@section('content')
    <div class="rounded-3xl border border-[#eee4ca] bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.bookings.update', $booking->id) }}">
            @csrf
            @method('PUT')
            @include('admin.bookings._form', ['submitLabel' => 'Update Booking', 'booking' => $booking])
        </form>
    </div>
@endsection

