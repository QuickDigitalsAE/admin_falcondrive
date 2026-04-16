@extends('admin.layouts.app')

@section('title', 'Create Blog')
@section('page_title', 'Create Blog')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('admin.blogs') }}" class="transition hover:text-[#9b7a28]">Blogs</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Create Blog</span>
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
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Blogs Management</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add New Blog</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Create a new blog with bilingual title, description, SEO details, schedule, and image.
                            </p>
                        </div>

                        <a href="{{ route('admin.blogs') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-5 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                            Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="title_en" type="text" name="title_en" value="{{ old('title_en') }}" placeholder="Title English"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('title_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="title_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('title_en') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Title EN</label>
                                </div>
                                @error('title_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="title_ar" type="text" name="title_ar" value="{{ old('title_ar') }}" placeholder="Title Arabic"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('title_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="title_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('title_ar') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Title AR</label>
                                </div>
                                @error('title_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="slug" type="text" name="slug" value="{{ old('slug') }}" placeholder="Slug"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('slug') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="slug" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('slug') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">Slug</label>
                                </div>
                                <p class="px-1 text-xs text-slate-500">Leave empty to auto-generate from Title EN.</p>
                                @error('slug')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="blog_schedule" type="datetime-local" name="blog_schedule"
                                        value="{{ old('blog_schedule') ? \Carbon\Carbon::parse(old('blog_schedule'))->format('Y-m-d\TH:i') : '' }}"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('blog_schedule') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="blog_schedule" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('blog_schedule') ? 'text-red-500' : 'text-slate-500' }}">Blog Schedule</label>
                                </div>
                                @error('blog_schedule')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="xl:col-span-2 min-w-0">
                            <div class="space-y-2">
                                <label for="blog_description_en" class="block px-1 text-xs font-medium {{ $errors->has('blog_description_en') ? 'text-red-500' : 'text-slate-500' }}">Blog Description EN</label>
                                <div class="resource-ckeditor-shell {{ $errors->has('blog_description_en') ? 'is-invalid' : '' }}">
                                    <textarea id="blog_description_en" name="blog_description_en" rows="6">{{ old('blog_description_en') }}</textarea>
                                </div>
                                @error('blog_description_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="xl:col-span-2 min-w-0">
                            <div class="space-y-2">
                                <label for="blog_description_ar" class="block px-1 text-xs font-medium {{ $errors->has('blog_description_ar') ? 'text-red-500' : 'text-slate-500' }}">Blog Description AR</label>
                                <div class="resource-ckeditor-shell {{ $errors->has('blog_description_ar') ? 'is-invalid' : '' }}">
                                    <textarea id="blog_description_ar" name="blog_description_ar" rows="6">{{ old('blog_description_ar') }}</textarea>
                                </div>
                                @error('blog_description_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="seo_title_en" type="text" name="seo_title_en" value="{{ old('seo_title_en') }}" placeholder="SEO Title English"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('seo_title_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="seo_title_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('seo_title_en') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">SEO Title EN</label>
                                </div>
                                @error('seo_title_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="seo_title_ar" type="text" name="seo_title_ar" value="{{ old('seo_title_ar') }}" placeholder="SEO Title Arabic"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('seo_title_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="seo_title_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('seo_title_ar') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">SEO Title AR</label>
                                </div>
                                @error('seo_title_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <textarea id="seo_brief_en" name="seo_brief_en" rows="4" placeholder="SEO Brief English"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('seo_brief_en') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('seo_brief_en') }}</textarea>
                                    <label for="seo_brief_en" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('seo_brief_en') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">SEO Brief EN</label>
                                </div>
                                @error('seo_brief_en')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <textarea id="seo_brief_ar" name="seo_brief_ar" rows="4" placeholder="SEO Brief Arabic"
                                        class="peer w-full rounded-[18px] border {{ $errors->has('seo_brief_ar') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-[#e5d7b1] focus:border-[#caa23c] focus:ring-[#f7e9b5]' }} bg-[#fffdf8] px-4 pt-6 pb-3 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4">{{ old('seo_brief_ar') }}</textarea>
                                    <label for="seo_brief_ar" class="pointer-events-none absolute left-4 top-2.5 z-10 bg-[#fffdf8] px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('seo_brief_ar') ? 'text-red-500' : 'text-slate-500 peer-focus:text-[#a27d20]' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">SEO Brief AR</label>
                                </div>
                                @error('seo_brief_ar')<p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-[#eadfbe] bg-gradient-to-br from-[#fffaf0] to-[#fffefb] p-4 sm:p-5">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                            <div class="flex shrink-0 items-center gap-4">
                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-[#d8c79d] bg-white shadow-sm">
                                    <img id="blogPreview" src="https://placehold.co/200x200/f8e8b2/5e450a?text=Blog" alt="Blog Preview" class="h-full w-full object-cover">
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800">Blog Image</h3>
                                    <p class="mt-1 text-sm text-slate-500">Upload a featured image for the blog.</p>
                                </div>
                            </div>

                            <div class="flex-1">
                                <label for="image" class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]">
                                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">Click to upload blog image</p>
                                    <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 4MB</p>
                                    <input id="image" type="file" name="image" accept=".png,.jpg,.jpeg,.webp" class="hidden">
                                </label>

                                <div class="mt-3">
                                    <span id="fileName" class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-[#eadfbe]">No file selected</span>
                                </div>

                                @error('image')<p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-[#f0e6ca] pt-6 sm:flex-row sm:flex-wrap">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#d6ab3d] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                            <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>
                            Save Blog
                        </button>

                        <button type="reset" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-rotate-right mr-2 text-[13px]"></i>
                            Reset
                        </button>

                        <a href="{{ route('admin.blogs') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-6 py-3 text-sm font-semibold text-[#7d6220] shadow-sm transition hover:bg-[#fff8e8]">
                            <i class="fa-solid fa-xmark mr-2 text-[13px]"></i>
                            Cancel
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
        const imageInput = document.getElementById('image');
        const blogPreview = document.getElementById('blogPreview');
        const fileName = document.getElementById('fileName');
        const defaultPreview = 'https://placehold.co/200x200/f8e8b2/5e450a?text=Blog';

        imageInput?.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) {
                blogPreview.src = defaultPreview;
                fileName.textContent = 'No file selected';
                return;
            }

            fileName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function (e) {
                blogPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endpush
