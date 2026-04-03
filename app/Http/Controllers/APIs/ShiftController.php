<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Shift_ViewAll', ['only' => ['getShifts']]);
        $this->middleware('permission:Shift_ViewMine', ['only' => ['getMyShifts']]);
        $this->middleware('permission:Shift_View', ['only' => ['editShift']]);
        $this->middleware('permission:Shift_Add', ['only' => ['postShift']]);
        $this->middleware('permission:Shift_Edit', ['only' => ['updateShift']]);
        $this->middleware('permission:Shift_Delete', ['only' => ['deleteShift']]);
        $this->middleware('permission:Shift_Revoke', ['only' => ['revokeShift']]);
    }

    // POST /shift — Create new shift
    public function postShift(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration' => 'nullable|integer',
            'grace_time' => 'nullable|integer',
            'alternate_saturday_off' => 'nullable|boolean',
            'type' => 'required|string',
        ]);

        $shift = Shift::create([
            'title' => $request->title,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'break_duration' => $request->break_duration ?? 0,
            'grace_time' => $request->grace_time ?? 0,
            'alternate_saturday_off' => $request->alternate_saturday_off ?? false,
            'user_id' => $request->user_id,
            'type' => $request->type,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Shift created successfully!',
            'data' => $shift,
        ], Response::HTTP_CREATED);
    }

    // GET /shift — List shifts (with optional export, deleted, search, pagination)
    public function getShifts(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted ? Shift::onlyTrashed() : Shift::query();
        $query->with([
            'user:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');

        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'break_duration' => $item->break_duration,
                'grace_time' => $item->grace_time,
                'alternate_saturday_off' => $item->alternate_saturday_off,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? null,
                'type' => $item->type,

                'created_by' => optional($item->createdByUser)->name ?? null,
                'updated_by' => optional($item->updatedByUser)->name ?? null,
                'deleted_by' => optional($item->deletedByUser)->name ?? null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $shifts = $query->get();
            $csvHeader = ['ID', 'Title', 'Start Time', 'End Time', 'Break Duration', 'Grace Time', 'Alt. Sat Off', 'User Name', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($shifts, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($shifts as $shift) {
                    $f = $format($shift);
                    $row = [
                        $f['id'], $f['title'], $f['start_time'], $f['end_time'],
                        $f['break_duration'], $f['grace_time'], $f['alternate_saturday_off'] ? 'Yes' : 'No',
                        $f['user_name'], $f['created_by'], $f['updated_by'],
                        $f['created_at'], $f['updated_at'],
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
                'Content-Disposition' => 'attachment; filename=shifts_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $shifts = $query->paginate($per_page);
        $formattedList = $shifts->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Shift list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $shifts->total(),
                    'per_page' => $shifts->perPage(),
                    'current_page' => $shifts->currentPage(),
                    'last_page' => $shifts->lastPage(),
                ],
            ],
        ]);
    }

    // GET /shift/my — Get shifts created by the logged-in user
    public function getMyShifts(Request $request)
    {
        $user = auth()->user();
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted
        ? Shift::onlyTrashed()->where('created_by', $user->id)
        : Shift::where('created_by', $user->id);

        $query->with([
            'user:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'break_duration' => $item->break_duration,
                'grace_time' => $item->grace_time,
                'alternate_saturday_off' => $item->alternate_saturday_off,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? null,
                'type' => $item->type,

                'created_by' => optional($item->createdByUser)->name ?? null,
                'updated_by' => optional($item->updatedByUser)->name ?? null,
                'deleted_by' => optional($item->deletedByUser)->name ?? null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $shifts = $query->get();
            $csvHeader = ['ID', 'Title', 'Start Time', 'End Time', 'Break Duration', 'Grace Time', 'Alt. Sat Off', 'User Name', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($shifts, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($shifts as $shift) {
                    $f = $format($shift);
                    $row = [
                        $f['id'], $f['title'], $f['start_time'], $f['end_time'],
                        $f['break_duration'], $f['grace_time'], $f['alternate_saturday_off'] ? 'Yes' : 'No',
                        $f['user_name'], $f['created_by'], $f['updated_by'],
                        $f['created_at'], $f['updated_at'],
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
                'Content-Disposition' => 'attachment; filename=shifts_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $shifts = $query->paginate($per_page);
        $formattedList = $shifts->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Shift list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $shifts->total(),
                    'per_page' => $shifts->perPage(),
                    'current_page' => $shifts->currentPage(),
                    'last_page' => $shifts->lastPage(),
                ],
            ],
        ]);
    }

    // GET /shift/{id} — Show shift detail
    public function editShift($id)
    {
        $shift = Shift::withTrashed()
            ->with([
                'user:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
            ])
            ->findOrFail($id);

        $data = [
                'id' => $shift->id,
                'title' => $shift->title,
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'break_duration' => $shift->break_duration,
                'grace_time' => $shift->grace_time,
                'alternate_saturday_off' => $shift->alternate_saturday_off,
                'user_id' => $shift->user_id,
                'user_name' => optional($shift->user)->name ?? null,
                'type' => $shift->type,

                'created_by' => optional($shift->createdByUser)->name ?? null,
                'updated_by' => optional($shift->updatedByUser)->name ?? null,
                'deleted_by' => optional($shift->deletedByUser)->name ?? null,
                'created_at' => $shift->created_at,
                'updated_at' => $shift->updated_at,
                'deleted_at' => $shift->deleted_at,
            ];

        return response()->json([
            'status' => true,
            'message' => 'Shift detail fetched successfully!',
            'data' => $data,
        ]);
    }

    // PUT /shift/{id} — Update shift
    public function updateShift(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $request->validate([
            'title' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration' => 'nullable|integer',
            'grace_time' => 'nullable|integer',
            'alternate_saturday_off' => 'nullable|boolean',
            'type' => 'nullable|string',
        ]);

        $data = $request->all();

        $data['break_duration'] = $request->break_duration ?? 0;
        $data['grace_time'] = $request->grace_time ?? 0;
        $data['alternate_saturday_off'] = $request->alternate_saturday_off ?? 0;
        $data['updated_by'] = Auth::id();

        $shift->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Shift updated successfully!',
            'data' => $shift,
        ]);
    }


    // DELETE /shift/{id} — Soft delete shift
    public function deleteShift($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->update(['deleted_by' => Auth::id()]);
        $shift->delete();

        return response()->json([
            'status' => true,
            'message' => 'Shift deleted successfully!',
        ]);
    }

    // PUT /shift/revoke/{id} — Restore soft-deleted shift
    public function revokeShift($id)
    {
        $shift = Shift::onlyTrashed()->findOrFail($id);
        $shift->restore();
        $shift->deleted_by = null;
        $shift->save();

        return response()->json([
            'status' => true,
            'message' => 'Shift restored successfully!',
        ]);
    }
}
