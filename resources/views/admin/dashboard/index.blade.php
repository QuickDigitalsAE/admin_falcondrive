@extends('admin.layouts.app')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of your system performance')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <p class="text-sm text-slate-500">Total Users</p>
            <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $totalUsers ?? 0 }}</h3>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <p class="text-sm text-slate-500">Active Users</p>
            <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $activeUsers ?? 0 }}</h3>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <p class="text-sm text-slate-500">Inactive Users</p>
            <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $inactiveUsers ?? 0 }}</h3>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
            <p class="text-sm text-slate-500">Roles</p>
            <h3 class="text-3xl font-bold text-slate-900 mt-2">{{ $totalRoles ?? 0 }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Latest Users</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500">
                        <th class="text-left px-4 py-3">Name</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-left px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($latestUsers ?? []) as $user)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection