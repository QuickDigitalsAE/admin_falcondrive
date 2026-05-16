@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('page_title', 'Notifications')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Notifications</span>
    </nav>
@endsection

@php
    $unreadCount = auth()->user()->adminNotifications()->unread()->count();
    $totalCount = auth()->user()->adminNotifications()->count();

    $colorClasses = [
        'amber' => 'bg-amber-100 text-amber-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'red' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-5">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rounded-3xl border border-[#eee4ca] bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#b89a4c]">Activity Center</p>
                        <h2 class="mt-1 text-xl font-semibold text-slate-900">Admin Notifications</h2>
                        <p class="mt-1 text-sm text-slate-500">Track recent admin actions, inquiry alerts, and system-side updates from one place.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.notifications.index', ['filter' => 'all']) }}"
                            class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-medium transition {{ $filter === 'all' ? 'border-[#c79a2b] bg-[#fff3cf] text-[#8b6916]' : 'border-[#eadfbe] bg-white text-slate-600 hover:bg-[#fffaf0]' }}">
                            All Notifications
                        </a>
                        <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}"
                            class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-medium transition {{ $filter === 'unread' ? 'border-[#c79a2b] bg-[#fff3cf] text-[#8b6916]' : 'border-[#eadfbe] bg-white text-slate-600 hover:bg-[#fffaf0]' }}">
                            Unread Only
                        </a>

                        @if($unreadCount > 0)
                            <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]">
                                    <i class="fas fa-check-double mr-2 text-[12px]"></i>
                                    Mark All Read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-[#eee4ca] bg-[#fffaf0] p-4 shadow-sm">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-[#f0e6ca] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b89a4c]">Unread</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $unreadCount }}</p>
                        <p class="mt-1 text-xs text-slate-500">Pending attention</p>
                    </div>
                    <div class="rounded-2xl border border-[#f0e6ca] bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b89a4c]">Total</p>
                        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $totalCount }}</p>
                        <p class="mt-1 text-xs text-slate-500">Stored alerts</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-[#eee4ca] bg-white shadow-sm">
            <div class="border-b border-[#f0e6ca] bg-[#fffaf0] px-5 py-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            {{ $filter === 'unread' ? 'Unread Notifications' : 'All Notifications' }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ $notifications->total() }} notification{{ $notifications->total() === 1 ? '' : 's' }} found
                        </p>
                    </div>
                    <p class="text-xs font-medium text-[#a47d1f]">
                        Page {{ $notifications->currentPage() }} of {{ max($notifications->lastPage(), 1) }}
                    </p>
                </div>
            </div>

            <div class="divide-y divide-[#f5eedb]">
                @forelse($notifications as $notification)
                    <div class="px-5 py-4 transition hover:bg-[#fffdf8] {{ $notification->read_at ? 'bg-white' : 'bg-[#fffaf5]' }}">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $colorClasses[$notification->color] ?? $colorClasses['amber'] }}">
                                    <i class="fas {{ $notification->icon ?: 'fa-bell' }} text-[14px]"></i>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-sm font-semibold text-slate-900">{{ $notification->title }}</h4>
                                        @if($notification->read_at)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">Read</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#fff0bf] px-2.5 py-1 text-[11px] font-semibold text-[#9b7a28]">Unread</span>
                                        @endif
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-500">
                                            {{ ucfirst($notification->category ?: 'general') }}
                                        </span>
                                    </div>

                                    @if($notification->message)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                                    @endif

                                    @if(data_get($notification->data, 'performed_by') || data_get($notification->data, 'name'))
                                        <p class="mt-2 text-xs font-medium text-slate-500">
                                            By: {{ data_get($notification->data, 'performed_by') ?: data_get($notification->data, 'name') }}
                                        </p>
                                    @endif

                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="far fa-clock text-[11px] text-[#b49543]"></i>
                                            {{ optional($notification->created_at)->format('d M Y, h:i A') }}
                                        </span>

                                        @if($notification->read_at)
                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fas fa-check-circle text-[11px] text-emerald-500"></i>
                                                Read at {{ optional($notification->read_at)->format('d M Y, h:i A') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2 lg:justify-end">
                                @if($notification->url)
                                    <a href="{{ $notification->url }}"
                                        class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-4 py-2 text-sm font-medium text-[#87671c] transition hover:bg-[#fff8e7]">
                                        <i class="fas fa-arrow-up-right-from-square mr-2 text-[12px]"></i>
                                        Open
                                    </a>
                                @endif

                                @if(!$notification->read_at)
                                    <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                                            <i class="fas fa-check mr-2 text-[12px]"></i>
                                            Mark as Read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#fff1c8] text-[#b49543]">
                            <i class="fas fa-bell-slash text-lg"></i>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">No notifications found</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $filter === 'unread' ? 'You are all caught up. There are no unread notifications right now.' : 'Notification activity will appear here when new admin updates are generated.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="border-t border-[#f0e6ca] bg-[#fffdf9] px-5 py-4">
                    {{ $notifications->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

