<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;

class ComplaintController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:Complaint_ViewAll', ['only' => ['getComplaints']]);
        $this->middleware('permission:Complaint_ViewMine', ['only' => ['getMyComplaints']]);
        $this->middleware('permission:Complaint_View', ['only' => ['editComplaint']]);
        $this->middleware('permission:Complaint_Add', ['only' => ['postComplaint']]);
        $this->middleware('permission:Complaint_Edit', ['only' => ['updateComplaint']]);
        $this->middleware('permission:Complaint_Delete', ['only' => ['deleteComplaint']]);
        $this->middleware('permission:Complaint_Revoke', ['only' => ['revokeComplaint']]);
    }

    public function getComplaints()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted ? Complaint::onlyTrashed() : Complaint::query();
        $query->with(['user', 'assignedToUser', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'body' => $item->body,
                'status' => $item->status,

                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? null,
                'assigned_to' => $item->assigned_to,
                'assigned_to_name' => optional($item->assignedToUser)->name ?? null,
                'created_by' => optional($item->createdByUser)->name ?? null,
                'updated_by' => optional($item->updatedByUser)->name ?? null,
                'deleted_by' => optional($item->deletedByUser)->name ?? null,

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };


        if ($is_export) {
            $complaints = $query->get();
            $csvHeader = ['ID', 'Title', 'Body', 'Status', 'User', 'Assigned To', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($complaints, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($complaints as $c) {
                    $f = $format($c);
                    $row = [
                        $f['id'], $f['title'], $f['body'], $f['status'], $f['user_name'], $f['assigned_to_name'],
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
                'Content-Disposition' => 'attachment; filename=complaints_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $complaints = $query->paginate($per_page);
        $formattedList = $complaints->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Complaint list fetched successfully.',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $complaints->currentPage(),
                    'last_page' => $complaints->lastPage(),
                    'per_page' => $complaints->perPage(),
                    'total' => $complaints->total(),
                ]
            ]
        ]);
    }

    public function getMyComplaints()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');
        $authId = Auth::id();

        $query = $is_deleted 
        ? Complaint::onlyTrashed()->where('created_by', $authId)
        : Complaint::where('created_by', $authId);

        $query->with(['user', 'assignedToUser', 'createdByUser', 'updatedByUser', 'deletedByUser'])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'body' => $item->body,
                'status' => $item->status,

                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? null,
                'assigned_to' => $item->assigned_to,
                'assigned_to_name' => optional($item->assignedToUser)->name ?? null,
                'created_by' => optional($item->createdByUser)->name ?? null,
                'updated_by' => optional($item->updatedByUser)->name ?? null,
                'deleted_by' => optional($item->deletedByUser)->name ?? null,

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };


        if ($is_export) {
            $complaints = $query->get();
            $csvHeader = ['ID', 'Title', 'Body', 'Status', 'User', 'Assigned To', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($complaints, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($complaints as $c) {
                    $f = $format($c);
                    $row = [
                        $f['id'], $f['title'], $f['body'], $f['status'], $f['user_name'], $f['assigned_to_name'],
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
                'Content-Disposition' => 'attachment; filename=complaints_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $complaints = $query->paginate($per_page);
        $formattedList = $complaints->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Complaint list fetched successfully.',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $complaints->currentPage(),
                    'last_page' => $complaints->lastPage(),
                    'per_page' => $complaints->perPage(),
                    'total' => $complaints->total(),
                ]
            ]
        ]);
    }

    public function postComplaint(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'required|string',
            'status'      => 'required|string|max:50',
            'user_id'     => 'required|integer',
            'assigned_to' => 'nullable|integer',
        ]);

        $authId = Auth::id();

        $complaint = Complaint::create([
            'title'       => $request->title,
            'body'        => $request->body,
            'status'      => $request->status,
            'user_id'     => $request->user_id,
            'assigned_to' => $request->assigned_to,
            'created_by'  => $authId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Complaint added successfully.',
            'data' => $complaint,
        ], Response::HTTP_CREATED);
    }

    public function editComplaint($id)
    {
        $complaint = Complaint::withTrashed()
            ->with([
                'user',
                'assignedToUser',
                'createdByUser', 
                'updatedByUser', 
                'deletedByUser',
                'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
                ])
            ->find($id);

        if (!$complaint) {
            return response()->json([
                'status' => false,
                'message' => 'Complaint not found.',
                'data' => []
            ], 404);
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'body' => $item->body,
                'status' => $item->status,

                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? null,
                'assigned_to' => $item->assigned_to,
                'assigned_to_name' => optional($item->assignedToUser)->name ?? null,

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
                
                'created_by' => optional($item->createdByUser)->name ?? null,
                'updated_by' => optional($item->updatedByUser)->name ?? null,
                'deleted_by' => optional($item->deletedByUser)->name ?? null,

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        return response()->json([
            'status' => true,
            'message' => 'Complaint fetched successfully.',
            'data' => $format($complaint),
        ]);
    }

    public function updateComplaint(Request $request, $id)
    {
        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'body'        => 'sometimes|required|string',
            'status'      => 'sometimes|required|string|max:50',
            'user_id'     => 'sometimes|required|integer',
            'assigned_to' => 'sometimes|nullable|integer',
        ]);

        $complaint = Complaint::withTrashed()->find($id);

        if (!$complaint) {
            return response()->json(['status' => false, 'message' => 'Complaint not found.', 'data' => []], 404);
        }

        $complaint->fill($request->only(['title', 'body', 'status', 'user_id', 'assigned_to']));
        $complaint->updated_by = Auth::id();
        $complaint->save();

        return response()->json([
            'status' => true,
            'message' => 'Complaint updated successfully.',
            'data' => $complaint,
        ]);
    }

    public function deleteComplaint($id)
    {
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json(['status' => false, 'message' => 'Complaint not found.', 'data' => []], 404);
        }

        $complaint->deleted_by = Auth::id();
        $complaint->save();
        $complaint->delete();

        return response()->json([
            'status' => true,
            'message' => 'Complaint deleted successfully.',
            'data' => []
        ]);
    }

    public function revokeComplaint($id)
    {
        $complaint = Complaint::withTrashed()->find($id);

        if (!$complaint) {
            return response()->json(['status' => false, 'message' => 'Complaint not found.', 'data' => []], 404);
        }

        if (!$complaint->deleted_at) {
            return response()->json(['status' => false, 'message' => 'Complaint is not deleted.', 'data' => []], 400);
        }

        $complaint->restore();
        $complaint->deleted_by = null;
        $complaint->save();

        return response()->json([
            'status' => true,
            'message' => 'Complaint restored successfully.',
            'data' => []
        ]);
    }
}
