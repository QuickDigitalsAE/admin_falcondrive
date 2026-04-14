@extends('admin.layouts.app')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">My Profile</span>
    </nav>
@endsection

@section('content')
    @php
        $avatarUrl = $user->profile_image_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User') . '&background=F8E8B2&color=5E450A&size=200';
        $blankAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'User') . '&background=F8E8B2&color=5E450A&size=200';
        $primaryRole = optional($user->roles->first())->name;
    @endphp

    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div
                class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-gradient-to-br from-[#071427] via-[#0c1d35] to-[#102443] text-white shadow-sm">
                <div class="grid gap-6 px-5 py-6 sm:px-6 lg:grid-cols-[1.6fr_1fr] lg:px-8">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-[0.26em] text-[#d4b563]">Account Center</p>
                        <h1 class="mt-3 text-[30px] font-bold leading-tight">Profile Details</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                            Update your identity, contact information, employee references, and avatar from one place.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('admin.account.settings') }}"
                                class="inline-flex items-center rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                                <i class="fas fa-gear mr-2 text-[13px]"></i>
                                Open Settings
                            </a>
                        </div>
                    </div>

                    <div
                        class="rounded-[24px] border border-white/10 bg-white/10 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <img src="{{ $avatarUrl }}" alt="Profile preview"
                                class="h-20 w-20 rounded-[22px] border border-white/15 object-cover shadow-lg">

                            <div class="min-w-0">
                                <p class="truncate text-lg font-semibold">{{ $user->name }}</p>
                                <p class="truncate text-sm text-slate-300">{{ $user->email }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        class="inline-flex rounded-full border border-[#d9b55a]/25 bg-[#d9b55a]/12 px-3 py-1 text-[11px] font-semibold text-[#f3d67b]">
                                        {{ $primaryRole ? ucwords(str_replace(['-', '_'], ' ', $primaryRole)) : 'No role assigned' }}
                                    </span>
                                    <span
                                        class="inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[11px] font-semibold text-white">
                                        {{ (int) $user->status === 1 ? 'Active Account' : 'Inactive Account' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.account.profile.update') }}" method="POST" enctype="multipart/form-data"
                autocomplete="off" class="grid gap-6 xl:grid-cols-[1.4fr_0.8fr]">
                @csrf
                @method('PUT')

                <input type="hidden" name="remove_profile_image" id="remove_profile_image" value="0">

                <div class="space-y-6">
                    <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                        <div class="border-b border-[#f0e6ca] bg-gradient-to-r from-[#fffaf0] to-[#fffdf8] px-5 py-5 sm:px-6">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Identity</p>
                            <h2 class="mt-1 text-[24px] font-bold text-slate-900">Personal Information</h2>
                            <p class="mt-1 text-sm text-slate-500">Keep your account details accurate for sign-in and internal records.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 xl:grid-cols-2">
                            <div class="space-y-2">
                                <label for="name" class="text-sm font-semibold text-slate-700">Full Name</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('name')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="email" class="text-sm font-semibold text-slate-700">Email Address</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('email') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('email')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-semibold text-slate-700">Phone</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('phone') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('phone')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="dob" class="text-sm font-semibold text-slate-700">Date of Birth</label>
                                <input id="dob" type="date" name="dob" value="{{ old('dob', $user->dob) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('dob') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('dob')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2 xl:col-span-2">
                                <label for="address" class="text-sm font-semibold text-slate-700">Address</label>
                                <textarea id="address" name="address" rows="4"
                                    class="w-full rounded-[18px] border {{ $errors->has('address') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                        <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-5 py-5 sm:px-6">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Records</p>
                            <h2 class="mt-1 text-[24px] font-bold text-slate-900">Official Information</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:p-6 xl:grid-cols-2">
                            <div class="space-y-2">
                                <label for="emp_id" class="text-sm font-semibold text-slate-700">Employee ID</label>
                                <input id="emp_id" type="text" name="emp_id" value="{{ old('emp_id', $user->emp_id) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('emp_id') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('emp_id')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="cnic" class="text-sm font-semibold text-slate-700">CNIC</label>
                                <input id="cnic" type="text" name="cnic" value="{{ old('cnic', $user->cnic) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('cnic') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('cnic')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="passport" class="text-sm font-semibold text-slate-700">Passport</label>
                                <input id="passport" type="text" name="passport" value="{{ old('passport', $user->passport) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('passport') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('passport')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="father_name" class="text-sm font-semibold text-slate-700">Father Name</label>
                                <input id="father_name" type="text" name="father_name" value="{{ old('father_name', $user->father_name) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('father_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('father_name')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="mother_name" class="text-sm font-semibold text-slate-700">Mother Name</label>
                                <input id="mother_name" type="text" name="mother_name" value="{{ old('mother_name', $user->mother_name) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('mother_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('mother_name')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="wife_name" class="text-sm font-semibold text-slate-700">Wife Name</label>
                                <input id="wife_name" type="text" name="wife_name" value="{{ old('wife_name', $user->wife_name) }}"
                                    class="w-full rounded-[18px] border {{ $errors->has('wife_name') ? 'border-red-300' : 'border-[#e5d7b1]' }} bg-[#fffdf8] px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-[#caa23c] focus:ring-4 focus:ring-[#f7e9b5]">
                                @error('wife_name')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                        <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-5 py-5">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Avatar</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">Profile Image</h2>
                        </div>

                        <div class="space-y-5 p-5">
                            <div
                                class="overflow-hidden rounded-[24px] border border-dashed border-[#d8c79d] bg-[#fffdf8] p-5 text-center">
                                <img id="profilePreview" src="{{ $avatarUrl }}" alt="Avatar preview"
                                    class="mx-auto h-32 w-32 rounded-[28px] border border-[#eadfbe] object-cover shadow-sm">
                                <p class="mt-4 text-sm font-semibold text-slate-800">Current preview</p>
                                <p class="mt-1 text-xs text-slate-500">Supported formats: JPG, PNG, JPEG, WEBP up to 2MB.</p>
                            </div>

                            <label for="profile_image"
                                class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('profile_image') ? 'border-red-300 bg-red-50/40' : 'border-[#d8c79d] bg-white hover:border-[#c79a2b] hover:bg-[#fff8e8]' }} px-5 py-8 text-center transition">
                                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#f8e8b2] text-[#a27d20]">
                                    <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-800">Upload new image</p>
                                <p id="fileName" class="mt-1 text-xs text-slate-500">No file selected</p>
                                <input id="profile_image" type="file" name="profile_image" accept=".png,.jpg,.jpeg,.webp"
                                    class="hidden">
                            </label>

                            @error('profile_image')
                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror

                            <button type="button" id="removeImageBtn"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-[#eadfbe] bg-white px-4 py-3 text-sm font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]">
                                <i class="fa-solid fa-trash-can mr-2 text-[13px]"></i>
                                Remove current image
                            </button>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[28px] border border-[#eadfbe] bg-white shadow-sm">
                        <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-5 py-5">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-[#b89a4c]">Account Snapshot</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">Current Access</h2>
                        </div>

                        <div class="space-y-4 p-5">
                            <div class="rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Primary Role</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">
                                    {{ $primaryRole ? ucwords(str_replace(['-', '_'], ' ', $primaryRole)) : 'No role assigned' }}
                                </p>
                            </div>

                            <div class="rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Status</p>
                                <p class="mt-2 text-sm font-semibold text-slate-900">
                                    {{ (int) $user->status === 1 ? 'Active' : 'Inactive' }}
                                </p>
                            </div>

                            <div class="rounded-[22px] border border-[#eadfbe] bg-[#fffdf8] px-4 py-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Quick Action</p>
                                <a href="{{ route('admin.account.settings') }}"
                                    class="mt-2 inline-flex items-center text-sm font-semibold text-[#9b7a28] transition hover:opacity-80">
                                    Open security settings
                                    <i class="fas fa-arrow-right ml-2 text-[12px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-[22px] bg-[#d6ab3d] px-6 py-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#c59626]">
                        <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>
                        Save Profile Changes
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileInput = document.getElementById('profile_image');
            const profilePreview = document.getElementById('profilePreview');
            const fileName = document.getElementById('fileName');
            const removeImageBtn = document.getElementById('removeImageBtn');
            const removeProfileImageInput = document.getElementById('remove_profile_image');
            const defaultPreview = @json($avatarUrl);
            const blankPreview = @json($blankAvatarUrl);

            if (profileInput) {
                profileInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];

                    if (!file) {
                        fileName.textContent = 'No file selected';
                        profilePreview.src = defaultPreview;
                        return;
                    }

                    removeProfileImageInput.value = '0';
                    fileName.textContent = file.name;

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        profilePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (removeImageBtn) {
                removeImageBtn.addEventListener('click', function () {
                    if (profileInput) {
                        profileInput.value = '';
                    }

                    if (removeProfileImageInput) {
                        removeProfileImageInput.value = '1';
                    }

                    fileName.textContent = 'Image will be removed after saving';
                    profilePreview.src = blankPreview;
                });
            }
        });
    </script>
@endpush
