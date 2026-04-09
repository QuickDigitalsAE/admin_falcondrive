@extends('admin.layouts.app')

@section('title', 'Create User')

@section('page_title', 'Create User')
@section('page_subtitle', 'Add a new user to FalconDrive admin panel')

@section('content')
    <section class="w-full pb-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-slate-400">Users</p>
                            <h1 class="text-[28px] font-bold leading-tight text-slate-900">Add User</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Create a new admin panel user with login access, role, profile image and status.
                            </p>
                        </div>

                        <a href="{{ route('admin.users') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i class="fa-solid fa-arrow-left mr-2 text-[13px]"></i>
                            Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data"
                    autocomplete="off" class="space-y-6 p-4 sm:p-6">
                    @csrf

                    {{-- Hidden fake fields to further discourage browser autofill --}}
                    <input type="text" name="fake_username" autocomplete="username" class="hidden" tabindex="-1">
                    <input type="password" name="fake_password" autocomplete="new-password" class="hidden" tabindex="-1">

                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                        <!-- Name -->
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Name"
                                        autocomplete="off" autocapitalize="off" spellcheck="false"
                                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('name') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-amber-500 focus:ring-amber-100' }} bg-white px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="name"
                                        class="pointer-events-none absolute left-4 top-2.5 z-10 bg-white px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('name') ? 'text-red-500' : 'text-slate-500 peer-focus:text-amber-600' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                                        Name
                                    </label>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                    <p class="text-xs text-slate-500">Enter full name of the user</p>
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700">Required</span>
                                </div>

                                @error('name')
                                    <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                                        placeholder="Email Address" autocomplete="off" autocapitalize="off"
                                        spellcheck="false"
                                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('email') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-amber-500 focus:ring-amber-100' }} bg-white px-4 pt-6 pb-2 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="email"
                                        class="pointer-events-none absolute left-4 top-2.5 z-10 bg-white px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('email') ? 'text-red-500' : 'text-slate-500 peer-focus:text-amber-600' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                                        Email Address
                                    </label>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                    <p class="text-xs text-slate-500">Used for login and notifications</p>
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700">Required</span>
                                </div>

                                @error('email')
                                    <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <select id="role_id" name="role_id" autocomplete="off"
                                        class="peer w-full min-w-0 appearance-none rounded-[18px] border {{ $errors->has('role_id') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-amber-500 focus:ring-amber-100' }} bg-white px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                        <option value="1">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ ucwords(str_replace(['-', '_'], ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <label for="role_id"
                                        class="pointer-events-none absolute left-4 top-2.5 z-10 bg-white px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('role_id') ? 'text-red-500' : 'text-slate-500' }}">
                                        Role
                                    </label>

                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                    <p class="text-xs text-slate-500">Select user access role from roles table</p>
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700">Required</span>
                                </div>

                                @error('role_id')
                                    <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="password" type="password" name="password" placeholder="Password"
                                        autocomplete="new-password"
                                        class="peer w-full min-w-0 rounded-[18px] border {{ $errors->has('password') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-amber-500 focus:ring-amber-100' }} bg-white px-4 pt-6 pb-2 pr-12 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                    <label for="password"
                                        class="pointer-events-none absolute left-4 top-2.5 z-10 bg-white px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('password') ? 'text-red-500' : 'text-slate-500 peer-focus:text-amber-600' }} transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs">
                                        Password
                                    </label>
                                    <button type="button"
                                        class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                        data-target="password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                    <p class="text-xs text-slate-500">Choose a secure password</p>
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700">Required</span>
                                </div>

                                @error('password')
                                    <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <input id="password_confirmation" type="password" name="password_confirmation"
                                        placeholder="Confirm Password" autocomplete="new-password"
                                        class="peer w-full min-w-0 rounded-[18px] border border-slate-300 bg-white px-4 pt-6 pb-2 pr-12 text-sm text-slate-800 placeholder-transparent outline-none transition duration-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-100 min-h-[58px]">
                                    <label for="password_confirmation"
                                        class="pointer-events-none absolute left-4 top-2.5 z-10 bg-white px-1 text-xs font-medium tracking-[0.02em] text-slate-500 transition-all peer-placeholder-shown:top-4 peer-placeholder-shown:text-sm peer-placeholder-shown:text-slate-400 peer-focus:top-2.5 peer-focus:text-xs peer-focus:text-amber-600">
                                        Confirm Password
                                    </label>
                                    <button type="button"
                                        class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                        data-target="password_confirmation">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                    <p class="text-xs text-slate-500">Re-enter password for confirmation</p>
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700">Required</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="min-w-0">
                            <div class="space-y-2">
                                <div class="relative">
                                    <select id="status" name="status" autocomplete="off"
                                        class="peer w-full min-w-0 appearance-none rounded-[18px] border {{ $errors->has('status') ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-amber-500 focus:ring-amber-100' }} bg-white px-4 pt-6 pb-2 pr-11 text-sm text-slate-800 outline-none transition duration-200 focus:ring-4 min-h-[58px]">
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>

                                    <label for="status"
                                        class="pointer-events-none absolute left-4 top-2.5 z-10 bg-white px-1 text-xs font-medium tracking-[0.02em] {{ $errors->has('status') ? 'text-red-500' : 'text-slate-500' }}">
                                        Status
                                    </label>

                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                    <p class="text-xs text-slate-500">Control whether the user can access the panel</p>
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-700">Required</span>
                                </div>

                                @error('status')
                                    <p class="px-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Profile Image Upload -->
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                            <div class="flex shrink-0 items-center gap-4">
                                <div id="profilePreviewWrapper"
                                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-white shadow-sm">
                                    <img id="profilePreview"
                                        src="https://ui-avatars.com/api/?name=User&background=F1F5F9&color=0F172A&size=200"
                                        alt="Profile Preview" class="h-full w-full object-cover">
                                </div>

                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800">Profile Image</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Upload a JPG, PNG, or WEBP image for the user avatar.
                                    </p>
                                </div>
                            </div>

                            <div class="flex-1">
                                <label for="profile_image"
                                    class="group flex cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed {{ $errors->has('profile_image') ? 'border-red-300 bg-red-50/40' : 'border-slate-300 bg-white hover:border-amber-400 hover:bg-amber-50/40' }} px-5 py-8 text-center transition">
                                    <div
                                        class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">Click to upload profile image</p>
                                    <p class="mt-1 text-xs text-slate-500">PNG, JPG, JPEG, WEBP up to 2MB</p>
                                    <input id="profile_image" type="file" name="profile_image"
                                        accept=".png,.jpg,.jpeg,.webp" class="hidden">
                                </label>

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <span id="fileName"
                                        class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 shadow-sm ring-1 ring-slate-200">
                                        No file selected
                                    </span>

                                    <button type="button" id="removeImageBtn"
                                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                                        <i class="fa-solid fa-trash-can mr-2"></i>
                                        Remove
                                    </button>
                                </div>

                                @error('profile_image')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[22px] border border-dashed border-slate-200 bg-slate-50/70 px-4 py-4 sm:px-5">
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                <i class="fa-solid fa-circle-info text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-slate-800">Before saving</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Make sure the email is unique, assign the correct role, upload a clean profile image,
                                    and keep a strong password for the new user.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:flex-wrap">
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-amber-400 px-6 py-3 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-amber-300">
                            <i class="fa-solid fa-floppy-disk mr-2 text-[13px]"></i>
                            Save User
                        </button>

                        <button type="reset"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i class="fa-solid fa-rotate-right mr-2 text-[13px]"></i>
                            Reset
                        </button>

                        <a href="{{ route('admin.users') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
            const profileInput = document.getElementById('profile_image');
            const profilePreview = document.getElementById('profilePreview');
            const fileName = document.getElementById('fileName');
            const removeImageBtn = document.getElementById('removeImageBtn');

            const defaultPreview = 'https://ui-avatars.com/api/?name=User&background=F1F5F9&color=0F172A&size=200';

            if (profileInput) {
                profileInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];

                    if (!file) {
                        profilePreview.src = defaultPreview;
                        fileName.textContent = 'No file selected';
                        return;
                    }

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
                    profilePreview.src = defaultPreview;
                    fileName.textContent = 'No file selected';
                });
            }

            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function () {
                    const target = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i');

                    if (!target) return;

                    if (target.type === 'password') {
                        target.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        target.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
@endpush