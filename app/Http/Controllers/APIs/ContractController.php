<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Contract;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Contract_ViewAll', ['only' => ['getContracts']]);
        $this->middleware('permission:Contract_ViewMine', ['only' => ['getMyContracts']]);
        $this->middleware('permission:Contract_View', ['only' => ['editContract']]);
        $this->middleware('permission:Contract_Add', ['only' => ['postContract']]);
        $this->middleware('permission:Contract_Edit', ['only' => ['updateContract']]);
        $this->middleware('permission:Contract_Delete', ['only' => ['deleteContract']]);
        $this->middleware('permission:Contract_Revoke', ['only' => ['revokeContract']]);
    }

    // POST /contract — Create new contract
    public function postContract(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'nullable|string',
            
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string',

            'basic_salary' => 'required|numeric',
            'allowance_hra' => 'nullable|numeric',
            'allowance_transport' => 'nullable|numeric',
            'allowance_attendance' => 'nullable|numeric',
            'allowance_medical' => 'nullable|numeric',

            'carry_forward' => 'nullable|integer',
            'annual_leave' => 'nullable|integer',
            'sick_leave' => 'nullable|integer',
            'parental_leave' => 'nullable|integer',
            'compensatory_leave' => 'nullable|integer',

            'medial_insurance' => 'nullable|string',
            'status' => 'required|string',
        ]);

         // Check if the user already has a contract
        $existingContract = Contract::where('user_id', $request->user_id)->first();
        if ($existingContract) {
            return response()->json([
                'status' => false,
                'message' => 'A contract already exists for this user.',
            ], Response::HTTP_CONFLICT);
        }

        $contract = Contract::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'basic_salary' => $request->basic_salary,
            
            'allowance_hra' => $request->allowance_hra ?? 0,
            'allowance_transport' => $request->allowance_transport ?? 0,
            'allowance_attendance' => $request->allowance_attendance ?? 0,
            'allowance_medical' => $request->allowance_medical ?? 0,

            'carry_forward' => $request->carry_forward,
            'annual_leave' => $request->annual_leave,
            'sick_leave' => $request->sick_leave,
            'parental_leave' => $request->parental_leave,
            'compensatory_leave' => $request->compensatory_leave,
            'medial_insurance' => $request->medial_insurance,
            'status' => $request->status,

            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Contract created successfully!',
            'data' => $contract,
        ], Response::HTTP_CREATED);
    }

    // GET /contract — List contracts (with optional export, deleted, search, pagination)
    public function getContracts(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted ? Contract::onlyTrashed() : Contract::query();
        $query->with([
            'user:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('type', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'title' => $item->title,
                'body' => $item->body,
                'type' => $item->type,

                'basic_salary' => $item->basic_salary,
                'allowance_hra' => $item->allowance_hra,
                'allowance_transport' => $item->allowance_transport,
                'allowance_attendance' => $item->allowance_attendance,
                'allowance_medical' => $item->allowance_medical,
                'carry_forward' => $item->carry_forward,
                'annual_leave' => $item->annual_leave,
                'sick_leave' => $item->sick_leave,
                'parental_leave' => $item->parental_leave,
                'compensatory_leave' => $item->compensatory_leave,
                'medial_insurance' => $item->medial_insurance,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'status' => $item->status,

                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $contracts = $query->get();
            $csvHeader = [
                'ID', 'User Name', 'Start Date', 'End Date', 'Type', 'Basic Salary', 'Carry Forward', 'Annual Leave',
                'Sick Leave', 'Parental Leave', 'Compensatory Leave', 'Medical Insurance', 'Status', 
                'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($contracts, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($contracts as $c) {
                    $f = $format($c);
                    $row = [
                        $f['id'], $f['user_name'], $f['start_date'], $f['end_date'], $f['type'], $f['basic_salary'],
                        $f['carry_forward'], $f['annual_leave'], $f['sick_leave'], $f['parental_leave'],
                        $f['compensatory_leave'], $f['medial_insurance'], $f['status'], $f['created_by'], $f['updated_by'],
                        $f['created_at'], $f['updated_at']
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
                'Content-Disposition' => 'attachment; filename=contracts_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $contracts = $query->paginate($per_page);
        $formattedList = $contracts->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Contract list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $contracts->currentPage(),
                    'per_page' => $contracts->perPage(),
                    'last_page' => $contracts->lastPage(),
                    'total' => $contracts->total(),
                ],
            ],
        ]);
    }

    public function getMyContracts(Request $request)
    {
        $authId = auth()->id();
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted
            ? Contract::onlyTrashed()->where('created_by', $authId)
            : Contract::where('created_by', $authId);

        $query->with([
            'user:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('type', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'title' => $item->title,
                'body' => $item->body,
                'type' => $item->type,
                'basic_salary' => $item->basic_salary,
                'allowance_hra' => $item->allowance_hra,
                'allowance_transport' => $item->allowance_transport,
                'allowance_attendance' => $item->allowance_attendance,
                'allowance_medical' => $item->allowance_medical,
                'carry_forward' => $item->carry_forward,
                'annual_leave' => $item->annual_leave,
                'sick_leave' => $item->sick_leave,
                'parental_leave' => $item->parental_leave,
                'compensatory_leave' => $item->compensatory_leave,
                'medial_insurance' => $item->medial_insurance,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'status' => $item->status,

                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $contracts = $query->get();
            $csvHeader = [
                'ID', 'User Name', 'Start Date', 'End Date', 'Type', 'Basic Salary', 'Carry Forward', 'Annual Leave',
                'Sick Leave', 'Parental Leave', 'Compensatory Leave', 'Medical Insurance', 'Status', 
                'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($contracts, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($contracts as $c) {
                    $f = $format($c);
                    $row = [
                        $f['id'], $f['user_name'], $f['start_date'], $f['end_date'], $f['type'], $f['basic_salary'],
                        $f['carry_forward'], $f['annual_leave'], $f['sick_leave'], $f['parental_leave'],
                        $f['compensatory_leave'], $f['medial_insurance'], $f['status'], $f['created_by'], $f['updated_by'],
                        $f['created_at'], $f['updated_at']
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
                'Content-Disposition' => 'attachment; filename=my_contracts_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $contracts = $query->paginate($per_page);
        $formattedList = $contracts->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My contract list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $contracts->total(),
                    'per_page' => $contracts->perPage(),
                    'current_page' => $contracts->currentPage(),
                    'last_page' => $contracts->lastPage(),
                ],
            ],
        ]);
    }

    public function editContract($id)
    {
        $contract = Contract::withTrashed()
            ->with([
                'user:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
            ])->findOrFail($id);

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'title' => $item->title,
                'body' => $item->body,
                'type' => $item->type,
                'basic_salary' => $item->basic_salary,
                'allowance_hra' => $item->allowance_hra,
                'allowance_transport' => $item->allowance_transport,
                'allowance_attendance' => $item->allowance_attendance,
                'allowance_medical' => $item->allowance_medical,
                'carry_forward' => $item->carry_forward,
                'annual_leave' => $item->annual_leave,
                'sick_leave' => $item->sick_leave,
                'parental_leave' => $item->parental_leave,
                'compensatory_leave' => $item->compensatory_leave,
                'medial_insurance' => $item->medial_insurance,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'status' => $item->status,

                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        return response()->json([
            'status' => true,
            'message' => 'Contract detail fetched successfully!',
            'data' => $format($contract),
        ]);
    }

    // PUT /contract/{id} — Update contract
    public function updateContract(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string',
            'body' => 'nullable|string',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string',

            'basic_salary' => 'required|numeric',
            'allowance_hra' => 'nullable|numeric',
            'allowance_transport' => 'nullable|numeric',
            'allowance_attendance' => 'nullable|numeric',
            'allowance_medical' => 'nullable|numeric',

            'carry_forward' => 'nullable|integer',
            'annual_leave' => 'nullable|integer',
            'sick_leave' => 'nullable|integer',
            'parental_leave' => 'nullable|integer',
            'compensatory_leave' => 'nullable|integer',

            'medial_insurance' => 'nullable|string',
            'status' => 'required|string',
        ]);

        // Prevent duplicate contract for the same user
        $exists = Contract::where('user_id', $request->user_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Another contract already exists for this user.',
            ], Response::HTTP_CONFLICT);
        }

        $data = $request->all();
        // Default 0 for null allowances
        $data['allowance_hra'] = $request->allowance_hra ?? 0;
        $data['allowance_transport'] = $request->allowance_transport ?? 0;
        $data['allowance_attendance'] = $request->allowance_attendance ?? 0;
        $data['allowance_medical'] = $request->allowance_medical ?? 0;
        $data['updated_by'] = Auth::id();

        $contract->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Contract updated successfully!',
            'data' => $contract,
        ]);
    }

    // DELETE /contract/{id} — Soft delete contract
    public function deleteContract($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update(['deleted_by' => Auth::id()]);
        $contract->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contract deleted successfully!',
        ]);
    }

    // PUT /contract/revoke/{id} — Restore soft-deleted contract
    public function revokeContract($id)
    {
        $contract = Contract::onlyTrashed()->findOrFail($id);
        $contract->restore();
        $contract->deleted_by = null;
        $contract->save();

        return response()->json([
            'status' => true,
            'message' => 'Contract restored successfully!',
        ]);
    }
}
