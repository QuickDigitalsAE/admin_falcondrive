@php
    $showAuditCard = \App\Support\SystemVisibility::isSuperAdminUser(auth()->user())
        && (
            !is_null(data_get($record, 'created_by')) ||
            !is_null(data_get($record, 'updated_by')) ||
            !is_null(data_get($record, 'deleted_by'))
        );
@endphp

@if($showAuditCard)
    <div class="mt-4 rounded-[24px] border border-[#eadfbe] bg-white p-5 shadow-sm">
        <div class="mb-4">
            <p class="text-[11px] uppercase tracking-[0.22em] text-[#b89a4c]">Audit Info</p>
            <h3 class="mt-1 text-lg font-semibold text-slate-900">Created / Updated / Deleted By</h3>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @if(!is_null(data_get($record, 'created_by')))
                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Created By</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ optional(data_get($record, 'createdByUser'))->name ?: 'N/A' }}
                    </p>
                </div>
            @endif

            @if(!is_null(data_get($record, 'updated_by')))
                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Updated By</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ optional(data_get($record, 'updatedByUser'))->name ?: 'N/A' }}
                    </p>
                </div>
            @endif

            @if(!is_null(data_get($record, 'deleted_by')))
                <div class="rounded-2xl border border-[#f0e6ca] bg-[#fffdf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#b89a4c]">Deleted By</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ optional(data_get($record, 'deletedByUser'))->name ?: 'N/A' }}
                    </p>
                </div>
            @endif
        </div>
    </div>
@endif
