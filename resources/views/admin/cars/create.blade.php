@extends('admin.layouts.app')

@section('title', 'Create Car')
@section('page_title', 'Create Car')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.cars') }}" class="transition hover:text-[#9b7a28]">Cars</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Car</span>
    </nav>
@endsection

@include('admin.layouts.partials.resource-ckeditor')

@push('styles')
<style>
    .car-chip-wrap .car-category-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        max-width: 100%;
        border-radius: 9999px;
        border: 1px solid #e7d39a;
        background: linear-gradient(135deg, #fff8e6 0%, #fff1c9 100%);
        padding: 0.55rem 0.85rem 0.55rem 1rem;
        color: #6f5315;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1;
        box-shadow: 0 8px 18px rgba(191, 150, 52, 0.12);
    }

    .car-chip-wrap .car-category-pill button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 9999px;
        border: 0;
        background: rgba(125, 98, 32, 0.1);
        color: #7d6220;
        font-size: 1rem;
        line-height: 1;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .car-chip-wrap .car-category-pill button:hover {
        background: #7d6220;
        color: #fff8e8;
        transform: scale(1.05);
    }

    .car-chip-wrap .car-empty-state {
        display: inline-flex;
        align-items: center;
        min-height: 2.5rem;
        color: #94a3b8;
        font-size: 0.8125rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Cars Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Car</h1>
                            <p class="mt-1 text-sm text-slate-500">Create a car record with pricing, specs, SEO, brand mapping, categories, and image gallery.</p>
                        </div>
                        <a href="{{ route('admin.cars') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @include('admin.cars._form', ['car' => null, 'submitLabel' => 'Save Car'])
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const brandSelect = document.getElementById('brand_id');
        const brandPickerWrap = document.getElementById('brand_picker_wrap');
        const brandPickerButton = document.getElementById('brand_picker_button');
        const brandPickerPanel = document.getElementById('brand_picker_panel');
        const brandPickerSearch = document.getElementById('brand_picker_search');
        const brandPickerList = document.getElementById('brand_picker_list');
        const brandPickerLabel = document.getElementById('brand_picker_label');
        const sortingSelect = document.getElementById('sorting');
        const featuredSelect = document.getElementById('featured');
        const featuredSortingWrap = document.getElementById('featuredSortingWrap');
        const featuredSortingSelect = document.getElementById('featured_sorting');
        const fleetSortingSelect = document.getElementById('fleet_sorting');
        const categorySelect = document.getElementById('category_ids');
        const categoryPickerWrap = document.getElementById('category_picker_wrap');
        const categoryPickerButton = document.getElementById('category_picker_button');
        const categoryPickerPanel = document.getElementById('category_picker_panel');
        const categoryPickerSearch = document.getElementById('category_picker_search');
        const categoryPickerList = document.getElementById('category_picker_list');
        const selectedTags = document.getElementById('selectedCategoryTags');
        const stockToggle = document.getElementById('stock');
        const stockToggleLabel = document.getElementById('stockToggleLabel');
        const mainImageInput = document.getElementById('main_image');
        const mainImagePreview = document.getElementById('mainImagePreview');
        const mainImageFileName = document.getElementById('mainImageFileName');
        const galleryInput = document.getElementById('images');
        const galleryPreviewGrid = document.getElementById('galleryPreviewGrid');
        const galleryFileName = document.getElementById('galleryFileName');
        const defaultMainImage = 'https://placehold.co/200x200/f8e8b2/5e450a?text=Main';
        const initialSortingValue = sortingSelect?.dataset.currentSorting || '';
        const sortOrdersBaseUrl = @json(url('/admin/cars/sort-orders'));
        const featuredSortOrdersUrl = @json(url('/admin/cars/featured-sort-orders'));
        const initialFeaturedSortingValue = featuredSortingSelect?.dataset.currentFeaturedSorting || '';
        const fleetSortOrdersUrl = @json(url('/admin/cars/fleet-sort-orders'));
        const initialFleetSortingValue = fleetSortingSelect?.dataset.currentFleetSorting || '';

        function getAvailableBrandOptions() {
            if (!brandSelect) return [];
            const query = (brandPickerSearch?.value || '').trim().toLowerCase();
            return Array.from(brandSelect.options).filter(option => {
                if (!option.value) return false;
                return query === '' || option.textContent.toLowerCase().includes(query);
            });
        }

        function closeBrandPicker() {
            brandPickerPanel?.classList.add('hidden');
        }

        function renderBrandPickerList() {
            if (!brandPickerList) return;
            const options = getAvailableBrandOptions();
            brandPickerList.innerHTML = options.length
                ? options.map(option => `<button type="button" class="brand-picker-option flex w-full items-center rounded-[14px] px-3 py-3 text-left text-sm text-slate-700 transition hover:bg-[#fff8e8] hover:text-[#7d6220]" data-brand-id="${option.value}">${option.textContent}</button>`).join('')
                : '<div class="px-3 py-3 text-sm text-slate-400">No brands found.</div>';
        }

        function syncBrandPickerLabel() {
            if (!brandSelect || !brandPickerLabel) return;
            brandPickerLabel.textContent = brandSelect.selectedOptions[0]?.textContent || 'Select Brand';
        }

        function renderSortingOptions(orders = [], selectedValue = '') {
            if (!sortingSelect || !brandSelect) return;

            if (!brandSelect.value) {
                sortingSelect.innerHTML = '<option value="">Select brand first</option>';
                sortingSelect.value = '';
                return;
            }

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
            if (!brandSelect?.value) {
                renderSortingOptions([], selectedValue);
                return;
            }

            fetch(`${sortOrdersBaseUrl}/${brandSelect.value}`)
                .then((response) => response.json())
                .then((orders) => {
                    renderSortingOptions(Array.isArray(orders) ? orders : [], selectedValue);
                })
                .catch(() => {
                    renderSortingOptions([], selectedValue);
                });
        }

        function renderFeaturedSortingOptions(orders = [], selectedValue = '') {
            if (!featuredSortingSelect) return;

            const normalizedOrders = orders.map(Number).filter(order => !Number.isNaN(order));
            const maxSortOrder = normalizedOrders.length ? Math.max(...normalizedOrders) : -1;
            const nextPosition = maxSortOrder + 1;
            const targetValue = selectedValue !== '' ? Number(selectedValue) : nextPosition;
            const optionValues = Array.from(new Set([...normalizedOrders, nextPosition, targetValue])).sort((a, b) => a - b);

            featuredSortingSelect.innerHTML = optionValues.map((value) => {
                const suffix = value === nextPosition ? ' (New slot)' : '';
                return `<option value="${value}">${value}${suffix}</option>`;
            }).join('');

            featuredSortingSelect.value = String(targetValue);
        }

        function renderFleetSortingOptions(orders = [], selectedValue = '') {
            if (!fleetSortingSelect) return;

            const normalizedOrders = orders.map(Number).filter(order => !Number.isNaN(order));
            const maxSortOrder = normalizedOrders.length ? Math.max(...normalizedOrders) : -1;
            const nextPosition = maxSortOrder + 1;
            const targetValue = selectedValue !== '' ? Number(selectedValue) : nextPosition;
            const optionValues = Array.from(new Set([...normalizedOrders, nextPosition, targetValue])).sort((a, b) => a - b);

            fleetSortingSelect.innerHTML = optionValues.map((value) => {
                const suffix = value === nextPosition ? ' (New slot)' : '';
                return `<option value="${value}">${value}${suffix}</option>`;
            }).join('');

            fleetSortingSelect.value = String(targetValue);
        }

        function toggleFeaturedSorting() {
            const isFeatured = featuredSelect?.value === '1';
            featuredSortingWrap?.classList.toggle('hidden', !isFeatured);

            if (!isFeatured && featuredSortingSelect) {
                featuredSortingSelect.innerHTML = '<option value="">Select Featured = Yes first</option>';
                featuredSortingSelect.value = '';
            }
        }

        function fetchFeaturedSortOrders(selectedValue = '') {
            const isFeatured = featuredSelect?.value === '1';

            if (!isFeatured) {
                toggleFeaturedSorting();
                return;
            }

            toggleFeaturedSorting();

            fetch(featuredSortOrdersUrl)
                .then((response) => response.json())
                .then((orders) => {
                    renderFeaturedSortingOptions(Array.isArray(orders) ? orders : [], selectedValue);
                })
                .catch(() => {
                    renderFeaturedSortingOptions([], selectedValue);
                });
        }

        function fetchFleetSortOrders(selectedValue = '') {
            fetch(fleetSortOrdersUrl)
                .then((response) => response.json())
                .then((orders) => {
                    renderFleetSortingOptions(Array.isArray(orders) ? orders : [], selectedValue);
                })
                .catch(() => {
                    renderFleetSortingOptions([], selectedValue);
                });
        }

        function getAvailableCategoryOptions() {
            if (!categorySelect) return [];
            const selectedValues = new Set(Array.from(categorySelect.selectedOptions).map(option => option.value));
            const query = (categoryPickerSearch?.value || '').trim().toLowerCase();
            return Array.from(categorySelect.options).filter(option => {
                const matchesQuery = query === '' || option.textContent.toLowerCase().includes(query);
                return !selectedValues.has(option.value) && matchesQuery;
            });
        }

        function closeCategoryPicker() {
            categoryPickerPanel?.classList.add('hidden');
        }

        function renderCategoryPickerList() {
            if (!categoryPickerList) return;
            const options = getAvailableCategoryOptions();
            categoryPickerList.innerHTML = options.length
                ? options.map(option => `<button type="button" class="category-picker-option flex w-full items-center rounded-[14px] px-3 py-3 text-left text-sm text-slate-700 transition hover:bg-[#fff8e8] hover:text-[#7d6220]" data-category-id="${option.value}">${option.textContent}</button>`).join('')
                : '<div class="px-3 py-3 text-sm text-slate-400">No categories found.</div>';
        }

        function renderCategoryTags() {
            if (!categorySelect || !selectedTags) return;
            const options = Array.from(categorySelect.selectedOptions);
            selectedTags.innerHTML = options.length
                ? options.map(option => `<span class="car-category-pill"><span>${option.textContent}</span><button type="button" class="remove-category-tag" data-category-id="${option.value}" aria-label="Remove ${option.textContent}">&times;</button></span>`).join('')
                : '<span class="car-empty-state">No categories selected yet.</span>';
            renderCategoryPickerList();
        }

        function setStockLabel() {
            if (!stockToggle || !stockToggleLabel) return;
            stockToggleLabel.textContent = stockToggle.checked ? 'On' : 'Off';
            stockToggleLabel.className = `text-sm font-semibold ${stockToggle.checked ? 'text-emerald-700' : 'text-slate-500'}`;
        }

        function renderGalleryPreviews(urls) {
            if (!galleryPreviewGrid) return;
            galleryPreviewGrid.innerHTML = urls.length ? urls.map(url => `<div class="overflow-hidden rounded-2xl border border-[#eadfbe] bg-white shadow-sm"><img src="${url}" alt="Gallery Preview" class="h-24 w-full object-cover"></div>`).join('') : '<div class="col-span-full text-xs text-slate-400">No gallery images selected.</div>';
        }

        brandPickerButton?.addEventListener('click', function () {
            brandPickerPanel?.classList.toggle('hidden');
            renderBrandPickerList();
            if (!brandPickerPanel?.classList.contains('hidden')) {
                brandPickerSearch?.focus();
            }
        });

        brandPickerSearch?.addEventListener('input', renderBrandPickerList);

        brandPickerList?.addEventListener('click', function (event) {
            const button = event.target.closest('.brand-picker-option');
            if (!button || !brandSelect) return;
            const option = Array.from(brandSelect.options).find(item => item.value === button.dataset.brandId);
            if (option) {
                brandSelect.value = option.value;
                syncBrandPickerLabel();
                fetchSortOrders('');
            }
            if (brandPickerSearch) {
                brandPickerSearch.value = '';
            }
            closeBrandPicker();
        });

        categoryPickerButton?.addEventListener('click', function () {
            categoryPickerPanel?.classList.toggle('hidden');
            renderCategoryPickerList();
            if (!categoryPickerPanel?.classList.contains('hidden')) {
                categoryPickerSearch?.focus();
            }
        });

        categoryPickerSearch?.addEventListener('input', renderCategoryPickerList);

        categoryPickerList?.addEventListener('click', function (event) {
            const button = event.target.closest('.category-picker-option');
            if (!button || !categorySelect) return;
            const option = Array.from(categorySelect.options).find(item => item.value === button.dataset.categoryId);
            if (option) {
                option.selected = true;
                renderCategoryTags();
            }
            if (categoryPickerSearch) {
                categoryPickerSearch.value = '';
            }
            closeCategoryPicker();
        });

        selectedTags?.addEventListener('click', function (event) {
            const button = event.target.closest('.remove-category-tag');
            if (!button || !categorySelect) return;
            const option = Array.from(categorySelect.options).find(item => item.value === button.dataset.categoryId);
            if (option) {
                option.selected = false;
                renderCategoryTags();
            }
        });

        document.addEventListener('click', function (event) {
            if (!brandPickerWrap?.contains(event.target)) {
                closeBrandPicker();
            }
            if (!categoryPickerWrap?.contains(event.target)) {
                closeCategoryPicker();
            }
        });

        stockToggle?.addEventListener('change', setStockLabel);
        featuredSelect?.addEventListener('change', function () {
            fetchFeaturedSortOrders('');
        });
        syncBrandPickerLabel();
        renderBrandPickerList();
        fetchSortOrders(initialSortingValue);
        fetchFeaturedSortOrders(initialFeaturedSortingValue);
        fetchFleetSortOrders(initialFleetSortingValue);
        renderCategoryTags();
        setStockLabel();
        renderGalleryPreviews([]);

        mainImageInput?.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) {
                mainImagePreview.src = defaultMainImage;
                mainImageFileName.textContent = 'No file selected';
                return;
            }
            mainImageFileName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function (e) { mainImagePreview.src = e.target.result; };
            reader.readAsDataURL(file);
        });

        galleryInput?.addEventListener('change', function (event) {
            const files = Array.from(event.target.files || []);
            if (!files.length) {
                galleryFileName.textContent = 'No files selected';
                renderGalleryPreviews([]);
                return;
            }
            galleryFileName.textContent = `${files.length} image(s) selected`;
            Promise.all(files.map(file => new Promise(resolve => {
                const reader = new FileReader();
                reader.onload = function (e) { resolve(e.target.result); };
                reader.readAsDataURL(file);
            }))).then(renderGalleryPreviews);
        });
    });
</script>
@endpush
