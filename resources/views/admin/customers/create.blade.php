@extends('admin.layouts.app')

@section('title', 'Create Customer')
@section('page_title', 'Create Customer')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.customers') }}" class="transition hover:text-[#9b7a28]">Customers</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Customer</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Customers Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Customer</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Create a customer record and sync it with the Speed system.
                            </p>
                        </div>

                        <a href="{{ route('admin.customers') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                            Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.customers.store') }}" method="POST" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @csrf
                    @include('admin.customers._form', ['customer' => null])

                    <div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                            <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>
                            Save Customer
                        </button>

                        <a href="{{ route('admin.customers') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-list mr-2 text-[13px]"></i>
                            All Customers
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function () {
                    const target = document.getElementById(this.dataset.target);
                    if (!target) return;
                    target.type = target.type === 'password' ? 'text' : 'password';
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.className = target.type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
                    }
                });
            });
        });
    </script>
@endpush

