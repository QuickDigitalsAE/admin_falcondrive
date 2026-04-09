<!DOCTYPE html>
<html lang="en">
@include('admin.layouts.partials.head')

<body class="h-screen overflow-hidden bg-slate-100 text-slate-800 antialiased">
    <div class="relative h-screen overflow-hidden bg-[#e9eef5]">
        <div class="absolute inset-0" style="
                background:
                    radial-gradient(circle at 15% 20%, rgba(255,210,31,0.12), transparent 20%),
                    radial-gradient(circle at 85% 15%, rgba(59,130,246,0.10), transparent 18%),
                    linear-gradient(135deg, #081224 0%, #13233f 34%, #eef2f7 34%, #eef2f7 100%);
            ">
        </div>

        <div class="absolute inset-0 opacity-[0.06]" style="
                background-image:
                    linear-gradient(rgba(255,255,255,0.8) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.8) 1px, transparent 1px);
                background-size: 28px 28px;
            ">
        </div>

        <div class="relative mx-auto flex h-screen max-w-7xl items-center justify-center px-4 py-4 sm:px-5 lg:px-6">
            <div
                class="grid w-full max-w-5xl overflow-hidden rounded-[30px] border border-white/40 bg-white/80 shadow-[0_24px_60px_rgba(15,23,42,0.16)] backdrop-blur-xl lg:grid-cols-[0.95fr_1.05fr]">

                <!-- Left Intro Panel -->
                <div
                    class="relative hidden overflow-hidden bg-[#07152d] px-7 py-8 text-white lg:flex lg:flex-col lg:justify-between xl:px-9 xl:py-9">
                    <div>
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-[20px] border border-white/10 bg-white shadow-xl">
                                <img src="{{ asset('images/falcon_logo.webp') }}" alt="FalconDrive"
                                    class="h-10 w-10 object-contain"
                                    onerror="this.style.display='none'; this.parentNode.innerHTML='<span class=\'text-xl font-black text-[#07152d]\'>FD</span>';">
                            </div>

                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-[0.32em] text-slate-300">FalconDrive CMS</p>
                                <h1 class="mt-1 text-3xl font-black leading-none text-white">Admin Access</h1>
                            </div>
                        </div>

                        <div class="mt-10">
                            <div
                                class="inline-flex items-center rounded-full border border-[#f5d86c]/20 bg-[#f5d86c]/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.26em] text-[#f5d86c]">
                                Secure Control Panel
                            </div>

                            <h2 class="mt-4 text-[2rem] font-black leading-tight text-white">
                                Access Your Dashboard
                            </h2>

                            <p class="mt-4 max-w-md text-[14px] leading-7 text-slate-300">
                                Sign in to manage FalconDrive users, roles, settings, and system operations from one
                                secure dashboard.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-[22px] border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#f5d86c]/15 text-[#f5d86c]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5Zm-3 8V6a3 3 0 1 1 6 0v3H9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Protected login</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-300">
                                        Only authorized users can access the admin system.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[22px] border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#f5d86c]/15 text-[#f5d86c]">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 12c2.76 0 5-2.24 5-5S14.76 2 12 2 7 4.24 7 7s2.24 5 5 5Zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Unified control</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-300">
                                        Manage users, permissions, and settings from one place.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pointer-events-none absolute inset-0">
                        <div class="absolute -left-14 top-14 h-32 w-32 rounded-full bg-[#f5d86c]/10 blur-3xl"></div>
                        <div class="absolute bottom-10 right-0 h-52 w-52 rounded-full bg-blue-400/10 blur-3xl"></div>
                    </div>
                </div>

                <!-- Right Form Panel -->
                <div class="bg-white/70 px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-7 xl:px-9">
                    <div class="mx-auto flex h-full w-full max-w-lg flex-col justify-center">
                        <div class="mb-5 lg:hidden">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#07152d] text-white shadow-lg">
                                    <span class="text-base font-black">FD</span>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.24em] text-slate-400">FalconDrive CMS
                                    </p>
                                    <h2 class="text-xl font-black text-slate-900">Admin Login</h2>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-[26px] border border-white/70 bg-white/90 p-1.5 shadow-[0_14px_32px_rgba(15,23,42,0.08)]">
                            <div class="rounded-[22px] border border-slate-100 bg-slate-50/80 p-5 sm:p-6">
                                <div class="mb-6">
                                    <div
                                        class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 12c2.76 0 5-2.24 5-5S14.76 2 12 2 7 4.24 7 7s2.24 5 5 5Zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5Z" />
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-[var(--color-secondary)]">Sign In</p>
                                    <h2 class="mt-2 text-[2rem] font-black tracking-tight text-slate-900">
                                        Access Your Dashboard
                                    </h2>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Enter your credentials to continue to the FalconDrive admin system.
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('login.submit') }}" class="space-y-5"
                                    autocomplete="off">
                                    @csrf

                                    <div>
                                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                                            Email Address
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M20 4H4a2 2 0 0 0-2 2v.4l10 6.25L22 6.4V6a2 2 0 0 0-2-2Zm2 4.75-9.47 5.92a1 1 0 0 1-1.06 0L2 8.75V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8.75Z" />
                                                </svg>
                                            </span>
                                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                                placeholder="Enter your email address"
                                                class="w-full rounded-2xl border bg-white py-3.5 pl-11 pr-4 text-sm text-slate-800 shadow-sm outline-none transition {{ $errors->has('email') ? 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-4 focus:ring-amber-100' }}"
                                                required>
                                        </div>

                                        @error('email')
                                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <label for="password" class="block text-sm font-semibold text-slate-700">
                                                Password
                                            </label>

                                            <a href="{{ route('password.request') }}"
                                                class="text-sm font-semibold text-[var(--color-secondary)] transition hover:opacity-80">
                                                Forgot Password?
                                            </a>
                                        </div>

                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 1a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2h-1V6a5 5 0 0 0-5-5Zm-3 8V6a3 3 0 1 1 6 0v3H9Z" />
                                                </svg>
                                            </span>
                                            <input id="password" type="password" name="password"
                                                placeholder="Enter your password"
                                                class="w-full rounded-2xl border bg-white py-3.5 pl-11 pr-11 text-sm text-slate-800 shadow-sm outline-none transition {{ $errors->has('password') ? 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-4 focus:ring-amber-100' }}"
                                                required>
                                            <button type="button"
                                                class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600"
                                                data-target="password">
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                        </div>

                                        @error('password')
                                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <label
                                            class="inline-flex items-center gap-3 text-sm font-medium text-slate-600">
                                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                                                class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                                            Remember me
                                        </label>
                                    </div>

                                    <button type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-2xl bg-[var(--color-primary)] px-6 py-3.5 text-[15px] font-bold text-slate-900 shadow-[0_10px_25px_rgba(245,216,108,0.28)] transition hover:translate-y-[-1px] hover:opacity-95">
                                        <svg class="mr-2 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M10 17l5-5-5-5v10Zm-6 3V4l10 8-10 8Zm11 0V4l10 8-10 8Z" />
                                        </svg>
                                        Login
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.partials.scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
</body>

</html>