<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Request as RequestModel;
use App\Traits\ImageUrlTrait;

class RequestController extends Controller
{
    use ImageUrlTrait;
    
    public function __construct()
    {
        $this->middleware('permission:Request_ViewAll', ['only' => ['getRequests']]);
        $this->middleware('permission:Request_ViewMine', ['only' => ['getMyRequests']]);
        $this->middleware('permission:Request_Add', ['only' => ['postRequest']]);
        $this->middleware('permission:Request_Edit', ['only' => ['updateRequest']]);
        $this->middleware('permission:Request_Delete', ['only' => ['deleteRequest']]);
        $this->middleware('permission:Request_Revoke', ['only' => ['revokeRequest']]);
    }

    public function postRequest(HttpRequest $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:department,id',
            'title' => 'required|string',
            'desc' => 'nullable|string',
            'body' => 'nullable|string',
            'status' => 'nullable|string',
            'category' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        $validated['created_by'] = Auth::id();

        $data = RequestModel::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Request created successfully.',
            'data' => $data,
        ], Response::HTTP_CREATED);
    }

    public function getRequests()
    {
        $perPage = getPerPage();
        $search = request()->query('search');
        $isDeleted = request()->query('is_deleted');
        $isExport = request()->query('is_export');

        $query = $isDeleted ? RequestModel::onlyTrashed() : RequestModel::query();

        $query->with([
            'user:id,name',
            'manager:id,name',
            'department:id,title',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name'
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('manager', function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('department', function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($req) {
            return [
                'id' => $req->id,
                'user_id' => $req->user_id,
                'user_name' => optional($req->user)->name,
                'manager_id' => $req->manager_id,
                'manager_name' => optional($req->manager)->name,
                'department_id' => $req->department_id,
                'department_name' => optional($req->department)->title,
                'title' => $req->title,
                'desc' => $req->desc,
                'body' => $req->body,
                'status' => $req->status,
                'category' => $req->category,
                'type' => $req->type,

                'created_by' => optional($req->createdByUser)->name,
                'updated_by' => optional($req->updatedByUser)->name,
                'deleted_by' => optional($req->deletedByUser)->name,
                'created_at' => $req->created_at,
                'updated_at' => $req->updated_at,
                'deleted_at' => $req->deleted_at,
            ];
        };

        if ($isExport) {
            $requests = $query->get();
            $csvHeader = [
                'ID', 'User', 'Manager', 'Title', 'Desc', 'Body',
                'Status', 'Category', 'Type', 'Department',
                'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($requests, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($requests as $req) {
                    $f = $format($req);
                    $row = [
                        $f['id'], $f['user_name'], $f['manager_name'],
                        $f['title'], $f['desc'], $f['body'],
                        $f['status'], $f['category'], $f['type'],
                        $f['department_name'], $f['created_by'], $f['updated_by'],
                        $f['created_at'], $f['updated_at']
                    ];

                    if ($isDeleted) {
                        $row[] = $f['deleted_by'];
                        $row[] = $f['deleted_at'];
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=requests_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $requests = $query->paginate($perPage);
        $formattedList = $requests->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Request list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $requests->total(),
                    'per_page' => $requests->perPage(),
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                ],
            ],
        ]);
    }

    public function getMyRequests()
    {
        $perPage = getPerPage();
        $search = request()->query('search');
        $isDeleted = request()->query('is_deleted');
        $isExport = request()->query('is_export');
        $userId = Auth::id();

        $query = $isDeleted ? RequestModel::onlyTrashed() : RequestModel::query();
        $query->where('created_by', $userId)
            ->with([
                'manager:id,name',
                'department:id,title',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name'
            ])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->orWhereHas('manager', function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('department', function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($req) {
            return [
                'id' => $req->id,
                'title' => $req->title,
                'desc' => $req->desc,
                'body' => $req->body,
                'status' => $req->status,
                'category' => $req->category,
                'type' => $req->type,
                'department_id' => $req->department_id,
                'department_name' => optional($req->department)->title,
                'manager_id' => $req->manager_id,
                'manager_name' => optional($req->manager)->name,
                'created_by' => optional($req->createdByUser)->name,
                'updated_by' => optional($req->updatedByUser)->name,
                'deleted_by' => optional($req->deletedByUser)->name,
                'created_at' => $req->created_at,
                'updated_at' => $req->updated_at,
                'deleted_at' => $req->deleted_at,
            ];
        };

        if ($isExport) {
            $requests = $query->get();
            $csvHeader = [
                'ID', 'Title', 'Desc', 'Body', 'Status', 'Category', 'Type', 'Department',
                'Manager', 'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($requests, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($requests as $req) {
                    $f = $format($req);
                    $row = [
                        $f['id'], $f['title'], $f['desc'], $f['body'], $f['status'],
                        $f['category'], $f['type'], $f['department_name'], $f['manager_name'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at']
                    ];

                    if ($isDeleted) {
                        $row[] = $f['deleted_by'];
                        $row[] = $f['deleted_at'];
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=my_requests_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $requests = $query->paginate($perPage);
        $formattedList = $requests->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My request list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $requests->total(),
                    'per_page' => $requests->perPage(),
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                ],
            ],
        ]);
    }

    public function editRequest($id)
    {
        $request = RequestModel::with([
            'user:id,name',
            'manager:id,name',
            'department:id,title',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
            'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
        ])->withTrashed()->find($id);

        if (!$request) {
            return response()->json([
                'status' => false,
                'message' => 'Request not found.',
            ], 404);
        }

        $data = [
            'id' => $request->id,
            'title' => $request->title,
            'desc' => $request->desc,
            'body' => $request->body,
            'status' => $request->status,
            'category' => $request->category,
            'type' => $request->type,
            'user_id' => $request->user_id,
            'user_name' => optional($request->user)->name,
            'manager_id' => $request->manager_id,
            'manager_name' => optional($request->manager)->name,
            'department_id' => $request->department_id,
            'department_name' => optional($request->department)->title,
            'created_by' => optional($request->createdByUser)->name,
            'updated_by' => optional($request->updatedByUser)->name,
            'deleted_by' => optional($request->deletedByUser)->name,
            
            'attachments' => collect($request->attachments)->map(function ($att) {
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

            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'deleted_at' => $request->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Request fetched successfully.',
            'data' => $data,
        ]);
    }

    public function updateRequest(HttpRequest $request, $id)
    {
        $record = RequestModel::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'desc' => 'nullable|string',
            'body' => 'nullable|string',
            'status' => 'nullable|string',
            'category' => 'nullable|string',
            'type' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:department,id',
        ]);

        $record->fill($request->only([
            'title', 'desc', 'body', 'status', 'category', 'type', 'department_id', 'manager_id'
        ]));

        $record->updated_by = Auth::id();
        $record->save();

        return response()->json([
            'status' => true,
            'message' => 'Request updated successfully.',
            'data' => $record,
        ]);
    }

    public function deleteRequest($id)
    {
        $record = RequestModel::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        $record->deleted_by = Auth::id();
        $record->save();
        $record->delete();

        return response()->json(['status' => true, 'message' => 'Request deleted.']);
    }

    public function revokeRequest($id)
    {
        $record = RequestModel::onlyTrashed()->find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Request not found.'], 404);
        }

        $record->restore();
        $record->deleted_by = null;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Request restored.']);
    }
}

