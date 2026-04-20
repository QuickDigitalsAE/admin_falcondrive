<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    private const TYPES = [
        'text',
        'textarea',
        'boolean',
        'number',
        'url',
        'email',
        'json',
        'color',
        'html',
        'rich_text_box',
        'image',
        'file',
    ];

    private const DEFAULT_GROUPS = [
        'site',
        'admin',
    ];

    public function __construct()
    {
        $this->middleware('permission:Setting_ViewAll|Setting_ViewMine', ['only' => ['index']]);
        $this->middleware('permission:Setting_ViewAll|Setting_ViewMine|Setting_View', ['only' => ['show']]);
        $this->middleware('permission:Setting_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Setting_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Setting_Delete', ['only' => ['destroy']]);
        $this->middleware('permission:Setting_Revoke', ['only' => ['restore']]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->can('Setting_ViewAll')) {
            return $this->listSettings($request);
        }

        if ($user->can('Setting_ViewMine')) {
            return $this->listSettings($request, true);
        }

        abort(403, 'You do not have permission to view settings.');
    }

    public function create()
    {
        return view('admin.settings.create', [
            'settingTypes' => self::TYPES,
            'groups' => $this->availableGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        $validated = $this->validateAssetField($request, $validated);

        Setting::create([
            'key' => $this->normalizeKey($validated['key']),
            'display_name' => $validated['display_name'],
            'value' => $this->resolveSettingValue($request, $validated),
            'details' => $validated['details'] ?? null,
            'type' => $validated['type'],
            'order' => $validated['order'] ?? 1,
            'group' => $validated['group'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.settings')->with('success', 'Setting added successfully.');
    }

    public function show(int $id)
    {
        $setting = Setting::withTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->find($id);

        if (!$setting) {
            return redirect()->route('admin.settings')->with('error', 'Setting not found.');
        }

        return view('admin.settings.show', compact('setting'));
    }

    public function edit(int $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return back()->with('error', 'Setting not found.');
        }

        return view('admin.settings.edit', [
            'setting' => $setting,
            'settingTypes' => self::TYPES,
            'groups' => $this->availableGroups(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return back()->with('error', 'Setting not found.');
        }

        $validated = $request->validate($this->validationRules($id));
        $validated = $this->validateAssetField($request, $validated);

        $setting->update([
            'key' => $this->normalizeKey($validated['key']),
            'display_name' => $validated['display_name'],
            'value' => $this->resolveSettingValue($request, $validated, $setting),
            'details' => $validated['details'] ?? null,
            'type' => $validated['type'],
            'order' => $validated['order'] ?? 1,
            'group' => $validated['group'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.settings')->with('success', 'Setting updated successfully.');
    }

    public function destroy(int $id)
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return back()->with('error', 'Setting not found.');
        }

        $setting->deleted_by = Auth::id();
        $setting->save();
        $setting->delete();

        return redirect()->route('admin.settings')->with('success', 'Setting deleted successfully.');
    }

    public function restore(int $id)
    {
        $setting = Setting::withTrashed()->find($id);

        if (!$setting) {
            return back()->with('error', 'Setting not found.');
        }

        if (is_null($setting->deleted_at)) {
            return back()->with('error', 'Setting is not deleted.');
        }

        $setting->restore();
        $setting->deleted_by = null;
        $setting->save();

        return redirect()->route('admin.settings')->with('success', 'Setting restored successfully.');
    }

    private function listSettings(Request $request, bool $onlyMine = false)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $group = trim((string) $request->query('group', ''));
        $type = trim((string) $request->query('type', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? Setting::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : Setting::with(['createdByUser', 'updatedByUser']);

        if ($onlyMine) {
            $query->where('created_by', Auth::id());
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('key', 'LIKE', "%{$search}%")
                    ->orWhere('display_name', 'LIKE', "%{$search}%")
                    ->orWhere('value', 'LIKE', "%{$search}%")
                    ->orWhere('details', 'LIKE', "%{$search}%")
                    ->orWhere('group', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        if ($group !== '') {
            $query->where('group', $group);
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        $query->orderBy('group')->orderBy('order')->orderBy('id');

        if ($isExport) {
            return $this->exportSettings($query, $isDeleted);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $items = $records->getCollection()->map(function (Setting $setting) use ($authUser) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'display_name' => $setting->display_name,
                    'value' => Str::limit((string) $setting->value, 100),
                    'value_full' => (string) $setting->value,
                    'value_url' => $setting->value_url,
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'order' => $setting->order,
                    'deleted_at' => optional($setting->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($setting->created_at)->format('d M Y, h:i A'),
                    ...$this->superAdminAuditMeta($setting, $authUser),
                    'show_url' => route('admin.settings.show', $setting->id),
                    'edit_url' => route('admin.settings.edit', $setting->id),
                    'delete_url' => route('admin.settings.delete', $setting->id),
                    'restore_url' => route('admin.settings.restore', $setting->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Setting_ViewAll') || $authUser->can('Setting_ViewMine') || $authUser->can('Setting_View'),
                        'can_edit' => $authUser->can('Setting_Edit'),
                        'can_delete' => $authUser->can('Setting_Delete'),
                        'can_restore' => $authUser->can('Setting_Revoke'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => $onlyMine ? 'My settings fetched successfully.' : 'Settings fetched successfully.',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $records->currentPage(),
                        'last_page' => $records->lastPage(),
                        'per_page' => $records->perPage(),
                        'total' => $records->total(),
                        'from' => $records->firstItem(),
                        'to' => $records->lastItem(),
                        'has_more_pages' => $records->hasMorePages(),
                    ],
                    'filters' => [
                        'search' => $search,
                        'group' => $group,
                        'type' => $type,
                        'is_deleted' => $isDeleted,
                    ],
                    'meta' => [
                        'groups' => $this->availableGroups(),
                        'types' => self::TYPES,
                    ],
                ],
            ]);
        }

        return view('admin.settings.index', [
            'groups' => $this->availableGroups(),
            'settingTypes' => self::TYPES,
        ]);
    }

    private function validationRules(?int $id = null): array
    {
        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('settings', 'key')->ignore($id)],
            'display_name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'type' => ['required', Rule::in(self::TYPES)],
            'order' => ['nullable', 'integer', 'min:1'],
            'group' => ['nullable', 'string', 'max:255'],
            'asset' => ['nullable'],
        ];
    }

    private function validateAssetField(Request $request, array $validated): array
    {
        $type = $validated['type'] ?? 'text';

        if ($type === 'image') {
            $request->validate([
                'asset' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            ]);
        } elseif ($type === 'file') {
            $request->validate([
                'asset' => ['nullable', 'file', 'max:10240'],
            ]);
        }

        return $validated;
    }

    private function resolveSettingValue(Request $request, array $validated, ?Setting $setting = null): ?string
    {
        $type = $validated['type'] ?? 'text';
        $currentValue = $setting?->value;

        if (!in_array($type, ['image', 'file'], true)) {
            if ($currentValue && in_array($setting?->type, ['image', 'file'], true) && $setting?->type !== $type) {
                $this->deleteStoredAsset($currentValue);
            }

            return $validated['value'] ?? null;
        }

        if (!$request->hasFile('asset')) {
            return $validated['value'] ?? $currentValue;
        }

        if ($currentValue && in_array($setting?->type, ['image', 'file'], true)) {
            $this->deleteStoredAsset($currentValue);
        }

        return $request->file('asset')->store("settings/{$type}", 'public');
    }

    private function deleteStoredAsset(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/\s+/', '_', $value);
        $value = preg_replace('/[^a-z0-9._-]/', '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return trim($value, '._-') ?: 'setting_key';
    }

    private function availableGroups(): array
    {
        return self::DEFAULT_GROUPS;
    }

    private function exportSettings($query, bool $isDeleted)
    {
        $records = $query->get();

        return response()->stream(function () use ($records, $isDeleted) {
            $file = fopen('php://output', 'w');
            $headers = ['ID', 'Key', 'Display Name', 'Type', 'Group', 'Order', 'Value', 'Created At'];

            if ($isDeleted) {
                $headers[] = 'Deleted At';
            }

            fputcsv($file, $headers);

            foreach ($records as $record) {
                $row = [
                    $record->id,
                    $record->key,
                    $record->display_name,
                    $record->type,
                    $record->group,
                    $record->order,
                    preg_replace('/\s+/', ' ', (string) $record->value),
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                ];

                if ($isDeleted) {
                    $row[] = optional($record->deleted_at)->format('Y-m-d H:i:s');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=settings.csv',
        ]);
    }
}

