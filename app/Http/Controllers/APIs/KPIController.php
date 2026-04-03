<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Kpi;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KPIController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:Kpi_ViewAll', ['only' => ['getKpis']]);
        $this->middleware('permission:Kpi_ViewMine', ['only' => ['getMyKpis']]);
        $this->middleware('permission:Kpi_View', ['only' => ['editKpi']]);
        $this->middleware('permission:Kpi_Add', ['only' => ['postKpi']]);
        $this->middleware('permission:Kpi_Edit', ['only' => ['updateKpi']]);
        $this->middleware('permission:Kpi_Delete', ['only' => ['deleteKpi']]);
        $this->middleware('permission:Kpi_Revoke', ['only' => ['revokeKpi']]);
    }

    public function postKpi(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'datetime' => 'required|date',
            'rating_by_user' => 'required|integer|min:1|max:10',
            'rating_by_manager' => 'required|integer|min:1|max:10',
            'manager_id' => 'required|integer',
            'comments' => 'nullable|string',
            'status' => 'required|string|max:50',
        ]);

        $authId = Auth::id();

        $kpi = Kpi::create([
            'user_id' => $request->user_id,
            'datetime' => $request->datetime,
            'rating_by_user' => $request->rating_by_user,
            'rating_by_manager' => $request->rating_by_manager,
            'manager_id' => $request->manager_id,
            'comments' => $request->comments,
            'status' => $request->status,
            'created_by' => $authId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'KPI added successfully.',
            'data' => $kpi,
        ], Response::HTTP_CREATED);
    }

    public function getKpis(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted ? Kpi::onlyTrashed() : Kpi::query();
        $query->with([
            'user:id,name',
            'manager:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name'
        ])->orderBy('datetime', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('comments', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('manager', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ,
                'manager_id' => $item->manager_id,
                'manager_name' => optional($item->manager)->name ,
                'datetime' => $item->datetime,
                'rating_by_user' => $item->rating_by_user,
                'rating_by_manager' => $item->rating_by_manager,
                'comments' => $item->comments,
                'status' => $item->status,
                'created_by' => optional($item->createdByUser)->name ,
                'updated_by' => optional($item->updatedByUser)->name ,
                'deleted_by' => optional($item->deletedByUser)->name ,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        // CSV Export
        if ($is_export) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User Name', 'Manager Name', 'Date', 'User Rating', 'Manager Rating',
                'Comments', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $r) {
                    $f = $format($r);
                    $row = [
                        $f['id'], $f['user_name'], $f['manager_name'], $f['datetime'],
                        $f['rating_by_user'], $f['rating_by_manager'], $f['comments'], $f['status'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at']
                    ];
                    if ($is_deleted) {
                        $row[] = $f['deleted_by'];
                        $row[] = $f['deleted_at'];
                    }
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=kpi_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($per_page);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'KPI list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                ],
            ],
        ]);
    }

    public function getMyKpis(Request $request)
    {
        $authId = auth()->id();
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted
            ? Kpi::onlyTrashed()->where('created_by', $authId)
            : Kpi::where('created_by', $authId);

        $query->with([
            'user:id,name',
            'manager:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name'
        ])->orderBy('datetime', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('comments', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('manager', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ,
                'manager_id' => $item->manager_id,
                'manager_name' => optional($item->manager)->name ,
                'datetime' => $item->datetime,
                'rating_by_user' => $item->rating_by_user,
                'rating_by_manager' => $item->rating_by_manager,
                'comments' => $item->comments,
                'status' => $item->status,
                'created_by' => optional($item->createdByUser)->name ,
                'updated_by' => optional($item->updatedByUser)->name ,
                'deleted_by' => optional($item->deletedByUser)->name ,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User Name', 'Manager Name', 'Date', 'User Rating', 'Manager Rating',
                'Comments', 'Status', 'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $r) {
                    $f = $format($r);
                    $row = [
                        $f['id'], $f['user_name'], $f['manager_name'], $f['datetime'],
                        $f['rating_by_user'], $f['rating_by_manager'], $f['comments'], $f['status'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at']
                    ];
                    if ($is_deleted) {
                        $row[] = $f['deleted_by'];
                        $row[] = $f['deleted_at'];
                    }
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=my_kpis_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($per_page);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My KPI list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                ],
            ],
        ]);
    }

    public function editKpi($id)
    {
        $kpi = Kpi::withTrashed()
            ->with([
                'user:id,name',
                'manager:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
                'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
            ])
            ->find($id);

        if (!$kpi) {
            return response()->json([
                'status' => false,
                'message' => 'KPI not found.',
                'data' => [],
            ], 404);
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ,
                'manager_id' => $item->manager_id,
                'manager_name' => optional($item->manager)->name ,
                'datetime' => $item->datetime,
                'rating_by_user' => $item->rating_by_user,
                'rating_by_manager' => $item->rating_by_manager,
                'comments' => $item->comments,
                'status' => $item->status,

                'attachments' => collect($item->attachments)->map(function ($att) {
                    return [
                        'id' => $att->id,
                        'table_primary_key' => $att->table_primary_key,
                        'table_name' => $att->table_name,
                        'type' => $att->type,
                        'category' => $att->category,
                        'title' => $att->title,
                        'desc' => $att->desc,
                        'url' => $att->url,
                        'full_url' => $att->url ? $this->getImageUrl($att->url) : null , // full path
                    ];
                }),

                'created_by' => optional($item->createdByUser)->name ,
                'updated_by' => optional($item->updatedByUser)->name ,
                'deleted_by' => optional($item->deletedByUser)->name ,
                
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        return response()->json([
            'status' => true,
            'message' => 'KPI detail fetched successfully!',
            'data' => $format($kpi),
        ]);
    }

    public function updateKpi(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'sometimes|integer',
            'datetime' => 'sometimes|date',
            'rating_by_user' => 'sometimes|integer|min:1|max:10',
            'rating_by_manager' => 'sometimes|integer|min:1|max:10',
            'manager_id' => 'sometimes|integer',
            'comments' => 'sometimes|nullable|string',
            'status' => 'sometimes|string|max:50',
        ]);

        $kpi = Kpi::withTrashed()->find($id);

        if (!$kpi) {
            return response()->json(['status' => false, 'message' => 'KPI not found.', 'data' => []], 404);
        }

        $kpi->fill($request->only([
            'user_id', 'datetime', 'rating_by_user', 'rating_by_manager',
            'manager_id', 'comments', 'status'
        ]));

        $kpi->updated_by = Auth::id();
        $kpi->save();

        return response()->json([
            'status' => true,
            'message' => 'KPI updated successfully.',
            'data' => $kpi,
        ]);
    }

    public function deleteKpi($id)
    {
        $kpi = Kpi::find($id);

        if (!$kpi) {
            return response()->json(['status' => false, 'message' => 'KPI not found.', 'data' => []], 404);
        }

        $kpi->deleted_by = Auth::id();
        $kpi->save();
        $kpi->delete();

        return response()->json([
            'status' => true,
            'message' => 'KPI deleted successfully.',
            'data' => [],
        ]);
    }

    public function revokeKpi($id)
    {
        $kpi = Kpi::withTrashed()->find($id);

        if (!$kpi) {
            return response()->json(['status' => false, 'message' => 'KPI not found.', 'data' => []], 404);
        }

        if (!$kpi->deleted_at) {
            return response()->json(['status' => false, 'message' => 'KPI is not deleted.', 'data' => []], 400);
        }

        $kpi->restore();
        $kpi->deleted_by = null;
        $kpi->save();

        return response()->json([
            'status' => true,
            'message' => 'KPI restored successfully.',
            'data' => [],
        ]);
    }
}
