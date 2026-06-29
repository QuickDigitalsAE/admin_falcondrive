<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReturnLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryReturnLocationController extends Controller
{
    public function index(Request $request)
    {
        return $this->listRecords($request);
    }

    public function create()
    {
        return view('admin.delivery-return-locations.create', [
            'location' => null,
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        DeliveryReturnLocation::create($data);

        return redirect()->route('admin.delivery-return-locations')->with('success', 'Location created successfully.');
    }

    public function show($id)
    {
        $location = DeliveryReturnLocation::withTrashed()->findOrFail($id);

        return view('admin.delivery-return-locations.show', compact('location'));
    }

    public function edit($id)
    {
        $location = DeliveryReturnLocation::findOrFail($id);

        return view('admin.delivery-return-locations.edit', [
            'location' => $location,
            'types' => $this->types(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $location = DeliveryReturnLocation::findOrFail($id);
        $data = $this->validated($request);
        $data['updated_by'] = Auth::id();

        $location->update($data);

        return redirect()->route('admin.delivery-return-locations')->with('success', 'Location updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $location = DeliveryReturnLocation::findOrFail($id);
        $location->update(['deleted_by' => Auth::id()]);
        $location->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Location deleted successfully.']);
        }

        return redirect()->route('admin.delivery-return-locations')->with('success', 'Location deleted successfully.');
    }

    public function restore(Request $request, $id)
    {
        $location = DeliveryReturnLocation::onlyTrashed()->findOrFail($id);
        $location->restore();
        $location->update(['deleted_by' => null, 'updated_by' => Auth::id()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Location restored successfully.']);
        }

        return redirect()->route('admin.delivery-return-locations')->with('success', 'Location restored successfully.');
    }

    private function listRecords(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isDeleted = $request->boolean('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted
            ? DeliveryReturnLocation::onlyTrashed()->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            : DeliveryReturnLocation::with(['createdByUser', 'updatedByUser']);

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('detail', 'LIKE', "%{$search}%")
                    ->orWhere('web_id', 'LIKE', "%{$search}%")
                    ->orWhere('longitude', 'LIKE', "%{$search}%")
                    ->orWhere('latitude', 'LIKE', "%{$search}%")
                    ->orWhere('price', 'LIKE', "%{$search}%")
                    ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->exportRecords($query, $isDeleted);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $items = $records->getCollection()->map(function (DeliveryReturnLocation $location) {
                return [
                    'id' => $location->id,
                    'title' => $location->title,
                    'detail' => $location->detail,
                    'web_id' => $location->web_id,
                    'longitude' => $location->longitude,
                    'latitude' => $location->latitude,
                    'price' => $location->price,
                    'type' => $location->type,
                    'deleted_at' => optional($location->deleted_at)->toDateTimeString(),
                    'created_at_human' => optional($location->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.delivery-return-locations.show', $location->id),
                    'edit_url' => route('admin.delivery-return-locations.edit', $location->id),
                    'delete_url' => route('admin.delivery-return-locations.delete', $location->id),
                    'restore_url' => route('admin.delivery-return-locations.restore', $location->id),
                    'created_by_name' => optional($location->createdByUser)->name,
                    'updated_by_name' => optional($location->updatedByUser)->name,
                    'deleted_by_name' => optional($location->deletedByUser)->name,
                    'permissions' => [
                        'can_view' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_restore' => true,
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Locations fetched successfully.',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $records->currentPage(),
                        'last_page' => $records->lastPage(),
                        'per_page' => $records->perPage(),
                        'total' => $records->total(),
                        'from' => $records->firstItem(),
                        'to' => $records->lastItem(),
                    ],
                ],
            ]);
        }

        return view('admin.delivery-return-locations.index', compact('records', 'search'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'detail' => ['nullable', 'string'],
            'web_id' => ['nullable', 'string', 'max:191'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'price' => ['required', 'string', 'max:191'],
            'type' => ['required', 'string', 'in:Delivery location,Return location'],
        ]);
    }

    private function types(): array
    {
        return [
            'Delivery location' => 'Delivery location',
            'Return location' => 'Return location',
        ];
    }

    private function exportRecords($query, bool $isDeleted): StreamedResponse
    {
        $fileName = ($isDeleted ? 'trashed-' : '') . 'delivery-return-locations-' . now()->format('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Detail', 'WebID', 'Longitude', 'Latitude', 'Price', 'Type', 'Created At', 'Updated At', 'Deleted At']);

            $query->chunk(200, function ($records) use ($handle) {
                foreach ($records as $record) {
                    fputcsv($handle, [
                        $record->id,
                        $record->title,
                        $record->detail,
                        $record->web_id,
                        $record->longitude,
                        $record->latitude,
                        $record->price,
                        $record->type,
                        optional($record->created_at)->format('Y-m-d H:i:s'),
                        optional($record->updated_at)->format('Y-m-d H:i:s'),
                        optional($record->deleted_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
