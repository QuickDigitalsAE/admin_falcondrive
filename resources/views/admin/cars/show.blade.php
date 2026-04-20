@extends('admin.layouts.app')

@section('title', 'Car Details')
@section('page_title', 'Car Details')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]"><i class="fas fa-house text-[11px]"></i><span class="ml-1">Dashboard</span></a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.cars') }}" class="transition hover:text-[#9b7a28]">Cars</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">View Car</span>
    </nav>
@endsection

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Cars Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">{{ $car->name_en }}</h1>
                            <p class="mt-1 text-sm text-slate-500">{{ $car->brand?->name_en }} · {{ $car->model }} · Stock {{ $car->stock }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @if (is_null($car->deleted_at) && auth()->user()->can('Car_Edit'))
                                <a href="{{ route('admin.cars.edit', $car->id) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]"><i class="fa-solid fa-pen-to-square mr-2 text-[13px]"></i>Edit Car</a>
                            @endif
                            <a href="{{ route('admin.cars') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]"><i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>Back to List</a>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-4">
                            <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-[#fffdf8] shadow-sm">
                                <img src="{{ $car->main_image_url ?: 'https://placehold.co/1000x620/f8e8b2/5e450a?text=Car' }}" alt="{{ $car->name_en }}" class="h-[320px] w-full object-cover">
                            </div>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @forelse ($car->gallery_image_urls as $galleryImage)
                                    <div class="overflow-hidden rounded-2xl border border-[#eadfbe] bg-white shadow-sm">
                                        <img src="{{ $galleryImage }}" alt="Gallery Image" class="h-24 w-full object-cover">
                                    </div>
                                @empty
                                    <div class="col-span-full rounded-2xl border border-dashed border-[#eadfbe] bg-[#fffdf8] px-4 py-8 text-center text-sm text-slate-400">No gallery images uploaded.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-5">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($car->categories as $category)
                                        <span class="inline-flex rounded-full border border-[#ead39a] bg-[#fff4d6] px-3 py-1 text-xs font-semibold text-[#7d6220]">{{ $category->name_en }}</span>
                                    @empty
                                        <span class="text-sm text-slate-400">No categories linked.</span>
                                    @endforelse
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Daily</p><p class="mt-1 font-semibold text-slate-800">{{ $car->price_daily }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Weekly</p><p class="mt-1 font-semibold text-slate-800">{{ $car->price_weekly }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Monthly</p><p class="mt-1 font-semibold text-slate-800">{{ $car->price_monthly }}</p></div>
                                    <div class="rounded-2xl border border-[#eadfbe] bg-white p-4"><p class="text-xs uppercase tracking-[0.18em] text-slate-400">Stock</p><p class="mt-1 font-semibold text-slate-800">{{ $car->stock }}</p></div>
                                </div>
                            </div>

                            <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                                <h3 class="text-sm font-semibold text-slate-900">Specifications</h3>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                    <div>Engine: <span class="font-semibold text-slate-800">{{ $car->engine ?: 'N/A' }}</span></div>
                                    <div>Seats: <span class="font-semibold text-slate-800">{{ $car->seats ?: 'N/A' }}</span></div>
                                    <div>Doors: <span class="font-semibold text-slate-800">{{ $car->doors ?: 'N/A' }}</span></div>
                                    <div>Luggage: <span class="font-semibold text-slate-800">{{ $car->luggage ?: 'N/A' }}</span></div>
                                    <div>Deposit: <span class="font-semibold text-slate-800">{{ $car->deposit ?: 'N/A' }}</span></div>
                                    <div>Featured: <span class="font-semibold text-slate-800">{{ $car->featured ? 'Yes' : 'No' }}</span></div>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ([
                                        'Cruise Control' => $car->cruise_control,
                                        'Bluetooth' => $car->bluetooth,
                                        'Automatic' => $car->automatic,
                                        'Parking Sensor' => $car->parking_sensor,
                                        'Navigation' => $car->navigation,
                                        'CarPlay' => $car->carplay,
                                        'Camera' => $car->camera,
                                    ] as $label => $enabled)
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $label }}: {{ $enabled ? 'Yes' : 'No' }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Description EN</h3>
                            <div class="prose prose-sm mt-4 max-w-none text-slate-600">{!! $car->description_en ?: '<p class="text-slate-400">No English description added.</p>' !!}</div>
                        </div>
                        <div class="rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
                            <h3 class="text-sm font-semibold text-slate-900">Description AR</h3>
                            <div class="prose prose-sm mt-4 max-w-none break-words text-right leading-8 text-slate-600 [overflow-wrap:anywhere] [&_h1]:text-right [&_h2]:text-right [&_h3]:text-right [&_h4]:text-right [&_li]:text-right [&_ol]:pr-6 [&_p]:my-0 [&_p]:mb-4 [&_p]:leading-8 [&_strong]:font-semibold [&_ul]:pr-6" dir="rtl">{!! $car->description_ar ?: '<p class="text-slate-400">No Arabic description added.</p>' !!}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('admin.layouts.partials.super-admin-audit-card', ['record' => $car])
@endsection
