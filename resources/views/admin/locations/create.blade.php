@extends('admin.layouts.app')

@section('title', 'Create Location')
@section('page_title', 'Create Location')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.locations') }}" class="transition hover:text-[#9b7a28]">Locations</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Location</span>
    </nav>
@endsection

@include('admin.layouts.partials.resource-ckeditor')

@push('styles')
<style>
    .location-car-pill { display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; background: #fff4d6; border: 1px solid #ead39a; color: #7d6220; font-size: 11px; font-weight: 600; padding: 6px 10px; }
    .location-car-pill button { display: inline-flex; height: 18px; width: 18px; align-items: center; justify-content: center; border-radius: 999px; border: 0; background: rgba(125, 98, 32, 0.12); color: #7d6220; cursor: pointer; }
    .location-car-pill button:hover { background: rgba(125, 98, 32, 0.2); }
</style>
@endpush

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0"><p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Locations Management</p><h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Location</h1><p class="mt-1 text-sm text-slate-500">Create bilingual location content with slug and SEO fields.</p></div>
                        <a href="{{ route('admin.locations') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
                <form action="{{ route('admin.locations.store') }}" method="POST" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @include('admin.locations._form', ['location' => null, 'submitLabel' => 'Save Location'])
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const carSelect = document.getElementById('car_ids');
        const carPickerWrap = document.getElementById('car_picker_wrap');
        const carPickerButton = document.getElementById('car_picker_button');
        const carPickerPanel = document.getElementById('car_picker_panel');
        const carPickerSearch = document.getElementById('car_picker_search');
        const carPickerList = document.getElementById('car_picker_list');
        const selectedTags = document.getElementById('selectedCarTags');

        function getAvailableCars() {
            if (!carSelect) return [];
            const selectedValues = new Set(Array.from(carSelect.selectedOptions).map(option => option.value));
            const query = (carPickerSearch?.value || '').trim().toLowerCase();
            return Array.from(carSelect.options).filter(option => {
                const matchesQuery = query === '' || option.textContent.toLowerCase().includes(query);
                return !selectedValues.has(option.value) && matchesQuery;
            });
        }

        function closeCarPicker() {
            carPickerPanel?.classList.add('hidden');
        }

        function renderPickerList() {
            if (!carPickerList) return;
            const options = getAvailableCars();
            carPickerList.innerHTML = options.length
                ? options.map(option => `<button type="button" class="admin-picker-option car-picker-option" data-car-id="${option.value}">
                        <span class="admin-picker-option-icon"><i class="fa-solid fa-car-side text-sm"></i></span>
                        <span class="min-w-0 flex-1">
                            <span class="admin-picker-option-title block truncate">${option.textContent}</span>
                            <span class="admin-picker-option-subtitle block">Tap to add</span>
                        </span>
                    </button>`).join('')
                : '<div class="admin-picker-empty">No cars found.</div>';
        }

        function renderTags() {
            if (!carSelect || !selectedTags) return;
            const options = Array.from(carSelect.selectedOptions);
            selectedTags.innerHTML = options.length
                ? options.map(option => `<span class="location-car-pill">${option.textContent}<button type="button" class="remove-car-tag" data-car-id="${option.value}" aria-label="Remove ${option.textContent}">&times;</button></span>`).join('')
                : '<span class="text-xs text-slate-400">No cars selected yet.</span>';
            renderPickerList();
        }

        carPickerButton?.addEventListener('click', function () {
            carPickerPanel?.classList.toggle('hidden');
            renderPickerList();
            if (!carPickerPanel?.classList.contains('hidden')) {
                carPickerSearch?.focus();
            }
        });

        carPickerSearch?.addEventListener('input', renderPickerList);

        carPickerList?.addEventListener('click', function (event) {
            const button = event.target.closest('.car-picker-option');
            if (!button || !carSelect) return;
            const option = Array.from(carSelect.options).find(item => item.value === button.dataset.carId);
            if (option) {
                option.selected = true;
                renderTags();
            }
            if (carPickerSearch) {
                carPickerSearch.value = '';
            }
            closeCarPicker();
        });

        selectedTags?.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-car-tag');
            if (!button || !carSelect) return;
            const option = Array.from(carSelect.options).find(item => item.value === button.dataset.carId);
            if (option) {
                option.selected = false;
                renderTags();
            }
        });

        document.addEventListener('click', function (event) {
            if (!carPickerWrap?.contains(event.target)) {
                closeCarPicker();
            }
        });

        renderTags();
    });
</script>
@endpush
