@extends('admin.layouts.app')

@section('title', 'Create Brand')
@section('page_title', 'Create Brand')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.brands') }}" class="transition hover:text-[#9b7a28]">Brands</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Brand</span>
    </nav>
@endsection

@include('admin.layouts.partials.resource-ckeditor')

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Brands Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Brand</h1>
                            <p class="mt-1 text-sm text-slate-500">Create a brand with bilingual content, slug, SEO metadata, and logo.</p>
                        </div>
                        <a href="{{ route('admin.brands') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                    </div>
                </div>
                <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @include('admin.brands._form', ['brand' => null, 'submitLabel' => 'Save Brand'])
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('logo');
            const preview = document.getElementById('logoPreview');
            const fileName = document.getElementById('fileName');
            const removeBtn = document.getElementById('removeImageBtn');
            const nameEnInput = document.getElementById('name_en');
            const slugInput = document.getElementById('slug');
            const sortingSelect = document.getElementById('sorting');
            const initialSortingValue = sortingSelect?.dataset.currentSorting || '';
            const sortOrdersUrl = @json(route('admin.brands.sort-orders'));
            const defaultPreview = @json('https://ui-avatars.com/api/?name=Brand&background=F8E8B2&color=5E450A&size=200');
            let lastAutoSlug = slugify(nameEnInput?.value || '');

            function slugify(value, preserveTrailingDash = false) {
                return String(value || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+/g, '')
                    .replace(preserveTrailingDash ? /$/ : /-+$/g, '');
            }

            function shouldAutoSyncSlug() {
                if (!slugInput) return false;
                const currentSlug = slugify(slugInput.value);
                return currentSlug === '' || currentSlug === lastAutoSlug;
            }

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

            nameEnInput?.addEventListener('input', function () {
                if (!slugInput) return;
                const nextAutoSlug = slugify(nameEnInput.value);
                if (shouldAutoSyncSlug()) {
                    slugInput.value = nextAutoSlug;
                }
                lastAutoSlug = nextAutoSlug;
            });

            slugInput?.addEventListener('input', function () {
                const sanitized = slugify(slugInput.value, true);
                if (slugInput.value !== sanitized) {
                    slugInput.value = sanitized;
                }
            });

            if (input) {
                input.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (!file) {
                        preview.src = defaultPreview;
                        fileName.textContent = 'No file selected';
                        return;
                    }
                    fileName.textContent = file.name;
                    const reader = new FileReader();
                    reader.onload = function (e) { preview.src = e.target.result; };
                    reader.readAsDataURL(file);
                });
            }
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    if (input) input.value = '';
                    preview.src = defaultPreview;
                    fileName.textContent = 'No file selected';
                });
            }

            fetchSortOrders(initialSortingValue);
        });
    </script>
@endpush
