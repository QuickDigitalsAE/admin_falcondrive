<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;

class PayrollController extends Controller
{
    use ImageUrlTrait;
    
    public function __construct()
    {
        $this->middleware('permission:Payroll_ViewAll', ['only' => ['getPayrolls']]);
        $this->middleware('permission:Payroll_ViewMine', ['only' => ['getMyPayrolls']]);
        $this->middleware('permission:Payroll_Add', ['only' => ['postPayroll']]);
        $this->middleware('permission:Payroll_Edit', ['only' => ['updatePayroll']]);
        $this->middleware('permission:Payroll_Delete', ['only' => ['deletePayroll']]);
        $this->middleware('permission:Payroll_Revoke', ['only' => ['revokePayroll']]);
    }

    public function postPayroll(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'contract_id' => 'nullable|exists:contract,id',
            'basic_salary' => 'required|numeric',
            'allowance_hra' => 'nullable|numeric',
            'allowance_transport' => 'nullable|numeric',
            'allowance_attendance' => 'nullable|numeric',
            'allowance_medical' => 'nullable|numeric',
            'deduction_late' => 'nullable|numeric',
            'deduction_loan' => 'nullable|numeric',
            'leaves_without_pay' => 'nullable|numeric',
            'net_salary' => 'required|numeric',
            'wps_code' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $payroll = Payroll::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Payroll record created.',
            'data' => $payroll
        ], Response::HTTP_CREATED);
    }

    public function getPayrolls(Request $request)
    {
        $perPage = getPerPage();
        $search = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted ? Payroll::onlyTrashed() : Payroll::query();
        $query->with([
            'user:id,name',
            'contract:id,title',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name'
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('contract', function ($q3) use ($search) {
                    $q3->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name,
                'contract_id' => optional($item->contract)->id,
                'contract_title' => optional($item->contract)->title,
                'basic_salary' => $item->basic_salary,
                'allowance_hra' => $item->allowance_hra,
                'allowance_transport' => $item->allowance_transport,
                'allowance_attendance' => $item->allowance_attendance,
                'allowance_medical' => $item->allowance_medical,
                'deduction_late' => $item->deduction_late,
                'deduction_loan' => $item->deduction_loan,
                'leaves_without_pay' => $item->leaves_without_pay,
                'net_salary' => $item->net_salary,
                'wps_code' => $item->wps_code,
                'created_by' => optional($item->createdByUser)->name,
                'updated_by' => optional($item->updatedByUser)->name,
                'deleted_by' => optional($item->deletedByUser)->name,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($isExport) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User Name', 'Contract Title', 'Basic Salary', 'Allowance HRA', 'Allowance Transport',
                'Allowance Attendance', 'Allowance Medical', 'Deduction Late', 'Deduction Loan',
                'Leaves Without Pay', 'Net Salary', 'WPS Code',
                'Created By', 'Created At', 'Updated By', 'Updated At'
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
                        $f['id'], $f['user_name'], $f['contract_title'], $f['basic_salary'], $f['allowance_hra'],
                        $f['allowance_transport'], $f['allowance_attendance'], $f['allowance_medical'],
                        $f['deduction_late'], $f['deduction_loan'], $f['leaves_without_pay'], $f['net_salary'],
                        $f['wps_code'], $f['created_by'], $f['created_at'], $f['updated_by'], $f['updated_at']
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
                'Content-Disposition' => 'attachment; filename=payrolls_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($perPage);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Payroll list fetched successfully!',
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

    public function getMyPayrolls(Request $request)
    {
        $authId = auth()->id();
        $perPage = getPerPage();
        $search = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');

        $query = $isDeleted ? Payroll::onlyTrashed() : Payroll::query();

        $query->where('created_by', $authId)
            ->with([
                'user:id,name',
                'contract:id,title',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name'
            ])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('contract', function ($q3) use ($search) {
                    $q3->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name,
                'contract_id' => optional($item->contract)->id,
                'contract_title' => optional($item->contract)->title,
                'basic_salary' => $item->basic_salary,
                'allowance_hra' => $item->allowance_hra,
                'allowance_transport' => $item->allowance_transport,
                'allowance_attendance' => $item->allowance_attendance,
                'allowance_medical' => $item->allowance_medical,
                'deduction_late' => $item->deduction_late,
                'deduction_loan' => $item->deduction_loan,
                'leaves_without_pay' => $item->leaves_without_pay,
                'net_salary' => $item->net_salary,
                'wps_code' => $item->wps_code,
                'created_by' => optional($item->createdByUser)->name,
                'updated_by' => optional($item->updatedByUser)->name,
                'deleted_by' => optional($item->deletedByUser)->name,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($isExport) {
            $records = $query->get();
            $csvHeader = [
                'ID', 'User Name', 'Contract Title', 'Basic Salary', 'Allowance HRA', 'Allowance Transport',
                'Allowance Attendance', 'Allowance Medical', 'Deduction Late', 'Deduction Loan',
                'Leaves Without Pay', 'Net Salary', 'WPS Code',
                'Created By', 'Created At', 'Updated By', 'Updated At'
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
                        $f['id'], $f['user_name'], $f['contract_title'], $f['basic_salary'], $f['allowance_hra'],
                        $f['allowance_transport'], $f['allowance_attendance'], $f['allowance_medical'],
                        $f['deduction_late'], $f['deduction_loan'], $f['leaves_without_pay'], $f['net_salary'],
                        $f['wps_code'], $f['created_by'], $f['created_at'], $f['updated_by'], $f['updated_at']
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
                'Content-Disposition' => 'attachment; filename=my_created_payrolls_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $results = $query->paginate($perPage);
        $formattedList = $results->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My created payrolls list fetched successfully!',
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

    public function editPayroll($id)
    {
        $payroll = Payroll::with([
            'user:id,name',
            'contract:id,title',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
            'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
        ])->withTrashed()->find($id);

        if (!$payroll) {
            return response()->json([
                'status' => false,
                'message' => 'Payroll not found.',
                'data' => null
            ], 404);
        }

        $formatted = [
            'id' => $payroll->id,
            'user_id' => $payroll->user_id,
            'user_name' => optional($payroll->user)->name,
            'contract_id' => optional($payroll->contract)->id,
            'contract_title' => optional($payroll->contract)->title,
            'basic_salary' => $payroll->basic_salary,
            'allowance_hra' => $payroll->allowance_hra,
            'allowance_transport' => $payroll->allowance_transport,
            'allowance_attendance' => $payroll->allowance_attendance,
            'allowance_medical' => $payroll->allowance_medical,
            'deduction_late' => $payroll->deduction_late,
            'deduction_loan' => $payroll->deduction_loan,
            'leaves_without_pay' => $payroll->leaves_without_pay,
            'net_salary' => $payroll->net_salary,
            'wps_code' => $payroll->wps_code,

            'attachments' => collect($payroll->attachments)->map(function ($att) {
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

            'created_by' => optional($payroll->createdByUser)->name,
            'updated_by' => optional($payroll->updatedByUser)->name,
            'deleted_by' => optional($payroll->deletedByUser)->name,

            'created_at' => $payroll->created_at,
            'updated_at' => $payroll->updated_at,
            'deleted_at' => $payroll->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Payroll fetched successfully!',
            'data' => $formatted
        ]);
    }

    public function updatePayroll(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);

        $request->validate([
            'contract_id' => 'nullable|exists:contract,id',
            'basic_salary' => 'nullable|numeric',
            'allowance_hra' => 'nullable|numeric',
            'allowance_transport' => 'nullable|numeric',
            'allowance_attendance' => 'nullable|numeric',
            'allowance_medical' => 'nullable|numeric',
            'deduction_late' => 'nullable|numeric',
            'deduction_loan' => 'nullable|numeric',
            'leaves_without_pay' => 'nullable|numeric',
            'net_salary' => 'nullable|numeric',
            'wps_code' => 'nullable|string',
        ]);

        $payroll->fill($request->all());
        $payroll->updated_by = Auth::id();
        $payroll->save();

        return response()->json([
            'status' => true,
            'message' => 'Payroll updated.',
            'data' => $payroll
        ]);
    }

    public function deletePayroll($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return response()->json(['status' => false, 'message' => 'Payroll not found.'], 404);
        }

        $payroll->deleted_by = Auth::id();
        $payroll->save();
        $payroll->delete();

        return response()->json(['status' => true, 'message' => 'Payroll deleted.']);
    }

    public function revokePayroll($id)
    {
        $payroll = Payroll::onlyTrashed()->find($id);

        if (!$payroll) {
            return response()->json(['status' => false, 'message' => 'Payroll not found.'], 404);
        }

        $payroll->restore();
        $payroll->deleted_by = null;
        $payroll->save();

        return response()->json(['status' => true, 'message' => 'Payroll restored.']);
    }
}
