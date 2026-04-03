<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;

class LeaveRequestController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:LeaveRequest_ViewAll', ['only' => ['getLeaveRequests']]);
        $this->middleware('permission:LeaveRequest_ViewMine', ['only' => ['getMyLeaveRequests']]);
        $this->middleware('permission:LeaveRequest_Add', ['only' => ['postLeaveRequest']]);
        $this->middleware('permission:LeaveRequest_Edit', ['only' => ['updateLeaveRequest']]);
        $this->middleware('permission:LeaveRequest_Delete', ['only' => ['deleteLeaveRequest']]);
        $this->middleware('permission:LeaveRequest_Revoke', ['only' => ['revokeLeaveRequest']]);
    }

    public function postLeaveRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'balance_annual' => 'nullable|numeric',
            'balance_sick' => 'nullable|numeric',
            'balance_parental' => 'nullable|numeric',
            'balance_comp' => 'nullable|numeric',
            'supporting_document' => 'boolean', // now boolean
            'reason' => 'nullable|string',
            'approver_user_id' => 'nullable|exists:users,id',
            'status' => 'nullable',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $leaveRequest = LeaveRequest::create([
            'user_id' => $request->user_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'no_of_days' => $this->calculateLeaveDays($request->start_date, $request->end_date),
            'supporting_document' => $request->supporting_document ?? false,
            'reason' => $request->reason,
            'approver_user_id' => $request->approver_user_id,
            'status' => $request->status ?? 'Pending',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave request submitted.',
            'data' => $leaveRequest,
        ], Response::HTTP_CREATED);
    }

    private function calculateLeaveDays($start_date, $end_date)
    {
        $start = \Carbon\Carbon::parse($start_date);
        $end = \Carbon\Carbon::parse($end_date);

        // +1 to include both start and end date
        return $start->diffInDays($end) + 1;
    }

    public function getLeaveRequests(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted ? LeaveRequest::onlyTrashed() : LeaveRequest::query();
        $query->with([
            'user:id,name',
            'approverUser:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('leave_type', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('approverUser', function ($q3) use ($search) {
                    $q3->where('name', 'LIKE', "%{$search}%");
                });
            });
        }


        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name,
                'leave_type' => $item->leave_type,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'no_of_days' => $item->no_of_days,
                'balance_annual' => $item->balance_annual,
                'balance_sick' => $item->balance_sick,
                'balance_parental' => $item->balance_parental,
                'balance_comp' => $item->balance_comp,
                'supporting_document' => $item->supporting_document,
                'reason' => $item->reason,
                'approver_user_id' => $item->approver_user_id,
                'approver_name' => optional($item->approverUser)->name,
                'status' => $item->status,
                'created_by' => optional($item->createdByUser)->name,
                'updated_by' => optional($item->updatedByUser)->name,
                'deleted_by' => optional($item->deletedByUser)->name,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        // CSV Export
        if ($is_export) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User', 'Leave Type', 'Start Date', 'End Date', 'No. of Days',
                'Balance Annual', 'Balance Sick', 'Balance Parental', 'Balance Comp',
                'Supporting Doc', 'Reason', 'Approver', 'Status',
                'Created By', 'Created At', 'Updated By', 'Updated At'
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
                        $f['id'], $f['user_name'], $f['leave_type'], $f['start_date'], $f['end_date'],
                        $f['no_of_days'], $f['balance_annual'], $f['balance_sick'], $f['balance_parental'],
                        $f['balance_comp'], $f['supporting_document'] ? 'Yes' : 'No', $f['reason'], $f['approver_name'],
                        $f['status'], $f['created_by'], $f['created_at'], $f['updated_by'], $f['updated_at']
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
                'Content-Disposition' => 'attachment; filename=leave_requests_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($per_page);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Leave request list fetched.',
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

    public function getMyLeaveRequests(Request $request)
    {
        $authId = auth()->id();
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted
            ? LeaveRequest::onlyTrashed()->where('created_by', $authId)
            : LeaveRequest::where('created_by', $authId);

        $query->with([
            'user:id,name',
            'approverUser:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('leave_type', 'LIKE', "%{$search}%")
                ->orWhereHas('approverUser', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name,
                'leave_type' => $item->leave_type,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'no_of_days' => $item->no_of_days,
                'balance_annual' => $item->balance_annual,
                'balance_sick' => $item->balance_sick,
                'balance_parental' => $item->balance_parental,
                'balance_comp' => $item->balance_comp,
                'supporting_document' => $item->supporting_document,
                'reason' => $item->reason,
                'approver_user_id' => $item->approver_user_id,
                'approver_name' => optional($item->approverUser)->name,
                'status' => $item->status,
                'created_by' => optional($item->createdByUser)->name,
                'updated_by' => optional($item->updatedByUser)->name,
                'deleted_by' => optional($item->deletedByUser)->name,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        // CSV Export
        if ($is_export) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User', 'Leave Type', 'Start Date', 'End Date', 'No. of Days',
                'Balance Annual', 'Balance Sick', 'Balance Parental', 'Balance Comp',
                'Supporting Doc', 'Reason', 'Approver', 'Status',
                'Created By', 'Created At', 'Updated By', 'Updated At'
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
                        $f['id'], $f['user_name'], $f['leave_type'], $f['start_date'], $f['end_date'],
                        $f['no_of_days'], $f['balance_annual'], $f['balance_sick'], $f['balance_parental'],
                        $f['balance_comp'], $f['supporting_document'] ? 'Yes' : 'No', $f['reason'], $f['approver_name'],
                        $f['status'], $f['created_by'], $f['created_at'], $f['updated_by'], $f['updated_at']
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
                'Content-Disposition' => 'attachment; filename=my_leave_requests_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($per_page);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My leave request list fetched.',
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

    public function editLeaveRequest($id)
    {
        $leaveRequest = LeaveRequest::withTrashed()
            ->with([
                'user:id,name',
                'approverUser:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
                'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
            ])
            ->find($id);

        if (!$leaveRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Leave request not found.',
            ], 404);
        }

        $data = [
            'id' => $leaveRequest->id,
            'user_id' => $leaveRequest->user_id,
            'user_name' => optional($leaveRequest->user)->name,
            'approver_user_id' => $leaveRequest->approver_user_id,
            'approver_user_name' => optional($leaveRequest->approverUser)->name,
            'leave_type' => $leaveRequest->leave_type,
            'start_date' => $leaveRequest->start_date,
            'end_date' => $leaveRequest->end_date,
            'no_of_days' => $leaveRequest->no_of_days,
            'balance_annual' => $leaveRequest->balance_annual,
            'balance_sick' => $leaveRequest->balance_sick,
            'balance_parental' => $leaveRequest->balance_parental,
            'balance_comp' => $leaveRequest->balance_comp,
            'supporting_document' => $leaveRequest->supporting_document,
            'reason' => $leaveRequest->reason,
            'status' => $leaveRequest->status,
            'created_by' => optional($leaveRequest->createdByUser)->name,
            'updated_by' => optional($leaveRequest->updatedByUser)->name,
            'deleted_by' => optional($leaveRequest->deletedByUser)->name,

            'attachments' => collect($leaveRequest->attachments)->map(function ($att) {
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
                
            'created_at' => $leaveRequest->created_at,
            'updated_at' => $leaveRequest->updated_at,
            'deleted_at' => $leaveRequest->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Leave request fetched.',
            'data' => $data,
        ]);
    }

    public function updateLeaveRequest(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        $request->validate([
            'clock_in' => 'nullable|date_format:H:i:s',
            'clock_out' => 'nullable|date_format:H:i:s',
            'leave_type' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'no_of_days' => 'nullable|numeric',
            'balance_annual' => 'nullable|numeric',
            'balance_sick' => 'nullable|numeric',
            'balance_parental' => 'nullable|numeric',
            'balance_comp' => 'nullable|numeric',
            'supporting_document' => 'boolean',
            'reason' => 'nullable|string',
            'approver_user_id' => 'nullable|exists:users,id',
            'status' => 'nullable',
        ]);

        $leaveRequest->fill($request->only([
            'leave_type',
            'start_date',
            'end_date',
            'supporting_document',
            'reason',
            'approver_user_id',
            'status',
        ]));

        if ($request->start_date && $request->end_date) {
            $leaveRequest->no_of_days = $this->calculateLeaveDays($request->start_date, $request->end_date);
        }

        $leaveRequest->updated_by = Auth::id();
        $leaveRequest->save();

        return response()->json([
            'status' => true,
            'message' => 'Leave request updated.',
            'data' => $leaveRequest,
        ]);
    }

    public function deleteLeaveRequest($id)
    {
        $leave = LeaveRequest::find($id);

        if (!$leave) {
            return response()->json(['status' => false, 'message' => 'Leave request not found.'], 404);
        }

        $leave->deleted_by = Auth::id();
        $leave->save();
        $leave->delete();

        return response()->json(['status' => true, 'message' => 'Leave request deleted.']);
    }

    public function revokeLeaveRequest($id)
    {
        $leave = LeaveRequest::onlyTrashed()->find($id);

        if (!$leave) {
            return response()->json(['status' => false, 'message' => 'Leave request not found.'], 404);
        }

        $leave->restore();
        $leave->deleted_by = null;
        $leave->save();

        return response()->json(['status' => true, 'message' => 'Leave request restored.']);
    }

}
