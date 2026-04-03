<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resignation;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;

class ResignationController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:Resignation_ViewAll', ['only' => ['getResignations']]);
        $this->middleware('permission:Resignation_ViewMine', ['only' => ['getMyResignations']]);
        $this->middleware('permission:Resignation_Add', ['only' => ['postResignation']]);
        $this->middleware('permission:Resignation_Edit', ['only' => ['updateResignation']]);
        $this->middleware('permission:Resignation_Delete', ['only' => ['deleteResignation']]);
        $this->middleware('permission:Resignation_Revoke', ['only' => ['revokeResignation']]);
    }

    public function postResignation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'resignation_date' => 'required|date',
            'last_working_day' => 'nullable|date',
            'reason' => 'nullable|string',
            'type' => 'nullable|string',
            'notice_period' => 'nullable|integer',
            'asset_return_checklist' => 'boolean',
            'leave_balance' => 'nullable|numeric',
            'air_ticket_entitlement' => 'boolean',
            'final_settlement_amount' => 'nullable|numeric',
            'it_approver_user_id' => 'nullable|exists:users,id',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $resignation = Resignation::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Resignation submitted.',
            'data' => $resignation
        ], Response::HTTP_CREATED);
    }

    public function getResignations(Request $request)
    {
        $perPage = getPerPage();
        $search = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted ? Resignation::onlyTrashed() : Resignation::query();

        $query->with([
            'user:id,name',
            'itApproverUser:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($r) {
            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'user_name' => optional($r->user)->name,
                'resignation_date' => $r->resignation_date,
                'last_working_day' => $r->last_working_day,
                'type' => $r->type,
                'notice_period' => $r->notice_period,
                'asset_return_checklist' => $r->asset_return_checklist,
                'leave_balance' => $r->leave_balance,
                'air_ticket_entitlement' => $r->air_ticket_entitlement,
                'final_settlement_amount' => $r->final_settlement_amount,
                'reason' => $r->reason,
                'it_approver_user_id' => $r->it_approver_user_id,
                'it_approver_name' => optional($r->itApproverUser)->name,

                'created_by' => optional($r->createdByUser)->name,
                'updated_by' => optional($r->updatedByUser)->name,
                'deleted_by' => optional($r->deletedByUser)->name,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
                'deleted_at' => $r->deleted_at,
            ];
        };

        // CSV Export
        if ($isExport) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User', 'Resignation Date', 'Last Working Day', 'Type',
                'Notice Period', 'Asset Return Checklist', 'Leave Balance',
                'Air Ticket Entitlement', 'Final Settlement Amount', 'Reason',
                'IT Approver', 'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $r) {
                    $f = $format($r);
                    $row = [
                        $f['id'], $f['user_name'], $f['resignation_date'], $f['last_working_day'],
                        $f['type'], $f['notice_period'], $f['asset_return_checklist'] ? 'Yes' : 'No', $f['leave_balance'],
                        $f['air_ticket_entitlement'] ? 'Yes' : 'No', $f['final_settlement_amount'], $f['reason'],
                        $f['it_approver_name'], $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=resignations_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($perPage);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Resignation list fetched successfully!',
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

    public function getMyResignations(Request $request)
    {
        $perPage = getPerPage();
        $search = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');

        $authId = auth()->id();

        $query = $isDeleted
            ? Resignation::onlyTrashed()->where('created_by', $authId)
            : Resignation::where('created_by', $authId);

        $query->with([
            'user:id,name',
            'itApproverUser:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name'
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'LIKE', "%{$search}%")
                ->orWhere('type', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($r) {
            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'user_name' => optional($r->user)->name,
                'resignation_date' => $r->resignation_date,
                'last_working_day' => $r->last_working_day,
                'type' => $r->type,
                'notice_period' => $r->notice_period,
                'asset_return_checklist' => $r->asset_return_checklist ? 'Yes' : 'No',
                'leave_balance' => $r->leave_balance,
                'air_ticket_entitlement' => $r->air_ticket_entitlement ? 'Yes' : 'No',
                'final_settlement_amount' => $r->final_settlement_amount,
                'reason' => $r->reason,
                'it_approver_user_id' => $r->it_approver_user_id,
                'it_approver_name' => optional($r->itApproverUser)->name,
                
                'created_by' => optional($r->createdByUser)->name,
                'updated_by' => optional($r->updatedByUser)->name,
                'deleted_by' => optional($r->deletedByUser)->name,
                
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
                'deleted_at' => $r->deleted_at,
            ];
        };

        // CSV Export
        if ($isExport) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'Resignation Date', 'Last Working Day', 'Type', 'Notice Period',
                'Asset Return Checklist', 'Leave Balance', 'Air Ticket Entitlement',
                'Final Settlement Amount', 'Reason', 'IT Approver',
                'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $r) {
                    $f = $format($r);
                    $row = [
                        $f['id'], $f['resignation_date'], $f['last_working_day'], $f['type'], $f['notice_period'],
                        $f['asset_return_checklist'], $f['leave_balance'], $f['air_ticket_entitlement'],
                        $f['final_settlement_amount'], $f['reason'], $f['it_approver_name'],
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
                'Content-Disposition' => 'attachment; filename=my_resignations_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($perPage);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My resignation list fetched successfully!',
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

    public function editResignation($id)
    {
        $resignation = Resignation::with([
            'user:id,name',
            'itApproverUser:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
            'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
        ])->withTrashed()->find($id);

        if (!$resignation) {
            return response()->json([
                'status' => false,
                'message' => 'Resignation not found.',
                'data' => null
            ], 404);
        }

        $formatted = [
            'id' => $resignation->id,
            'user_id' => $resignation->user_id,
            'user_name' => optional($resignation->user)->name,
            'resignation_date' => $resignation->resignation_date,
            'last_working_day' => $resignation->last_working_day,
            'type' => $resignation->type,
            'notice_period' => $resignation->notice_period,
            'asset_return_checklist' => $resignation->asset_return_checklist ? 'Yes' : 'No',
            'leave_balance' => $resignation->leave_balance,
            'air_ticket_entitlement' => $resignation->air_ticket_entitlement ? 'Yes' : 'No',
            'final_settlement_amount' => $resignation->final_settlement_amount,
            'reason' => $resignation->reason,
            'it_approver_user_id' => $resignation->it_approver_user_id,
            'it_approver_name' => optional($resignation->itApproverUser)->name,

            'attachments' => collect($resignation->attachments)->map(function ($att) {
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

            'created_by' => optional($resignation->createdByUser)->name,
            'updated_by' => optional($resignation->updatedByUser)->name,
            'deleted_by' => optional($resignation->deletedByUser)->name,

            'created_at' => $resignation->created_at,
            'updated_at' => $resignation->updated_at,
            'deleted_at' => $resignation->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Resignation details fetched successfully!',
            'data' => $formatted
        ]);
    }

    public function updateResignation(Request $request, $id)
    {
        $resignation = Resignation::findOrFail($id);

        $request->validate([
            'resignation_date' => 'nullable|date',
            'last_working_day' => 'nullable|date',
            'reason' => 'nullable|string',
            'type' => 'nullable|string',
            'notice_period' => 'nullable|integer',
            'asset_return_checklist' => 'boolean',
            'leave_balance' => 'nullable|numeric',
            'air_ticket_entitlement' => 'boolean',
            'final_settlement_amount' => 'nullable|numeric',
            'it_approver_user_id' => 'nullable|exists:users,id',
        ]);

        $resignation->fill($request->all());
        $resignation->updated_by = Auth::id();
        $resignation->save();

        return response()->json([
            'status' => true,
            'message' => 'Resignation updated.',
            'data' => $resignation
        ]);
    }

    public function deleteResignation($id)
    {
        $resignation = Resignation::find($id);

        if (!$resignation) {
            return response()->json(['status' => false, 'message' => 'Resignation not found.'], 404);
        }

        $resignation->deleted_by = Auth::id();
        $resignation->save();
        $resignation->delete();

        return response()->json(['status' => true, 'message' => 'Resignation deleted.']);
    }

    public function revokeResignation($id)
    {
        $resignation = Resignation::onlyTrashed()->find($id);

        if (!$resignation) {
            return response()->json(['status' => false, 'message' => 'Resignation not found.'], 404);
        }

        $resignation->restore();
        $resignation->deleted_by = null;
        $resignation->save();

        return response()->json(['status' => true, 'message' => 'Resignation restored.']);
    }
}
