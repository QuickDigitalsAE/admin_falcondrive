@extends('admin.layouts.app')

@section('title', 'Edit Highlight')
@section('page_title', 'Edit Highlight')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.highlights') }}" class="transition hover:text-[#9b7a28]">Highlights</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Edit Highlight</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8"><div class="mx-auto w-full max-w-7xl"><div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm"><div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6"><div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"><div class="min-w-0"><p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Highlights Management</p><h1 class="text-[28px] font-bold leading-tight text-slate-900">Edit Highlight</h1><p class="mt-1 text-sm text-slate-500">Update titles and the highlight image.</p></div><div class="flex flex-wrap gap-3"><a href="{{ route('admin.highlights.show', $highlight->id) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-eye mr-2 text-[13px]"></i>View Highlight</a><a href="{{ route('admin.highlights') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a></div></div></div><form action="{{ route('admin.highlights.update', $highlight->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6 p-4 sm:p-6">@method('PUT')@include('admin.highlights._form', ['submitLabel' => 'Update Highlight'])</form></div></div></section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const preview = document.getElementById('highlightPreview');
    const fileName = document.getElementById('fileName');
    const sortingSelect = document.getElementById('sorting');
    const initialSortingValue = sortingSelect?.dataset.currentSorting || '';
    const sortOrdersUrl = @json(route('admin.highlights.sort-orders', ['ignore_highlight_id' => $highlight->id]));
    const defaultPreview = @json($highlight->image_url ?: 'https://placehold.co/200x200/f8e8b2/5e450a?text=Highlight');

    function renderSortingOptions(orders = [], selectedValue = '') {
        if (!sortingSelect) return;

        const normalizedOrders = orders.map(Number).filter(order => !Number.isNaN(order));
        const maxSortOrder = normalizedOrders.length ? Math.max(...normalizedOrders) : -1;
        const nextPosition = maxSortOrder + 1;
        const targetValue = selectedValue !== '' ? Number(selectedValue) : nextPosition;
        const optionValues = Array.from(new Set([...normalizedOrders, nextPosition, targetValue])).sort((a, b) => a - b);

        sortingSelect.innerHTML = optionValues.map((value) => {
            const suffix = value === nextPosition ? ' (New slot)' : '';
            return `<option value="${value}">${value}${suffix}</option>`;
        }).join('');

        sortingSelect.value = String(targetValue);
    }

    function fetchSortOrders(selectedValue = '') {
        fetch(sortOrdersUrl)
            .then((response) => response.json())
            .then((orders) => renderSortingOptions(Array.isArray(orders) ? orders : [], selectedValue))
            .catch(() => renderSortingOptions([], selectedValue));
    }

    imageInput?.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (!file) { preview.src = defaultPreview; fileName.textContent = @json($highlight->image ? basename($highlight->image) : 'No file selected'); return; }
        fileName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) { preview.src = e.target.result; };
        reader.readAsDataURL(file);
    });

    fetchSortOrders(initialSortingValue);
});
</script>
@endpush
