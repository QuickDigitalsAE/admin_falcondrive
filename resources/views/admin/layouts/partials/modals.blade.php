<div id="deleteModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 p-4">
    <div class="flex items-center justify-center min-h-full">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900">Delete Record</h3>
                <p class="text-sm text-slate-500 mt-1">Are you sure you want to delete this record?</p>
            </div>

            <div class="p-6 flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50">
                    Cancel
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-600 text-white hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>