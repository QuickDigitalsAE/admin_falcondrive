<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Attendance;
use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ImageUrlTrait;

class AttendanceController extends Controller
{
    use ImageUrlTrait;
    
    public function __construct()
    {
        $this->middleware('permission:Attendance_ViewAll', ['only' => ['getAttendances']]);
        $this->middleware('permission:Attendance_ViewMine', ['only' => ['getMyAttendances']]);
        $this->middleware('permission:Attendance_Add', ['only' => ['postAttendance']]);
        $this->middleware('permission:Attendance_Edit', ['only' => ['updateAttendance']]);
        $this->middleware('permission:Attendance_Delete', ['only' => ['deleteAttendance']]);
        $this->middleware('permission:Attendance_Revoke', ['only' => ['revokeAttendance']]);
    }

    public function postAttendance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'date' => 'required|date',
            'shift_id' => 'required|integer|exists:shift,id',
            'clock_in' => 'nullable|date_format:H:i:s',
            'clock_out' => 'nullable|date_format:H:i:s',
            'is_late' => 'boolean',
            'late_minutes' => 'nullable|integer',
            'late_reason' => 'nullable|string',
            'is_early_departure' => 'boolean',
            'early_departure_minutes' => 'nullable|integer',
            'adjustment_request_status' => 'required|string',
            'late_deduction_applied' => 'boolean',
        ]);

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'shift_id' => $request->shift_id,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'is_late' => $request->is_late,
            'late_minutes' => $request->late_minutes,
            'late_reason' => $request->late_reason,
            'is_early_departure' => $request->is_early_departure,
            'early_departure_minutes' => $request->early_departure_minutes,
            'adjustment_request_status' => $request->adjustment_request_status,
            'late_deduction_applied' => $request->late_deduction_applied,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Attendance record created.',
            'data' => $attendance,
        ], 201);
    }

    public function getAttendances()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted ? Attendance::onlyTrashed() : Attendance::query();
        $query->with(['user:id,name', 'shift:id,title', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('date', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('shift', function ($q3) use ($search) {
                    $q3->where('title', 'LIKE', "%{$search}%");
                })->orWhere('late_reason', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'shift_id' => $item->shift_id,
                'shift_title' => optional($item->shift)->title ?? '',
                'date' => $item->date,
                'clock_in' => $item->clock_in,
                'clock_out' => $item->clock_out,
                'total_worked_hours' => $item->total_worked_hours,
                'is_late' => $item->is_late,
                'late_minutes' => $item->late_minutes,
                'late_reason' => $item->late_reason,
                'is_early_departure' => $item->is_early_departure,
                'early_departure_minutes' => $item->early_departure_minutes,
                'adjustment_request_status' => $item->adjustment_request_status,
                'late_deduction_applied' => $item->late_deduction_applied,
                'created_by_user' => optional($item->createdByUser)->name ?? '',
                'updated_by_user' => optional($item->updatedByUser)->name ?? '',
                'deleted_by_user' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $records = $query->get();
            $csvHeader = ['ID', 'User', 'Shift', 'Date', 'Clock In', 'Clock Out', 'Worked Hours', 'Late', 'Late Minutes', 'Late Reason', 'Early Departure', 'Early Minutes', 'Adjustment Request', 'Deduction', 'Created By', 'Created At', 'Updated By', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $item) {
                    $formatted = $format($item);
                    $row = [
                        $formatted['id'],
                        $formatted['user_name'],
                        $formatted['shift_title'],
                        $formatted['date'],
                        $formatted['clock_in'],
                        $formatted['clock_out'],
                        $formatted['total_worked_hours'],
                        $formatted['is_late'] ? 'Yes' : 'No',
                        $formatted['late_minutes'],
                        $formatted['late_reason'],
                        $formatted['is_early_departure'] ? 'Yes' : 'No',
                        $formatted['early_departure_minutes'],
                        $formatted['adjustment_request_status'],
                        $formatted['late_deduction_applied'] ? 'Yes' : 'No',
                        $formatted['created_by_user'],
                        $formatted['created_at'],
                        $formatted['updated_by_user'],
                        $formatted['updated_at'],
                    ];

                    if ($is_deleted) {
                        $row[] = $formatted['deleted_by_user'];
                        $row[] = $formatted['deleted_at'];
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=attendances_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $records = $query->paginate($per_page);
        $formattedList = $records->map($format);

        return response()->json([
            'status' => true,
            'message' => 'Attendances list fetched.',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ],
            ],
        ]);
    }

    public function getMyAttendances()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');
        $auth_id = auth()->id();

        $query = $is_deleted ? Attendance::onlyTrashed() : Attendance::query();
        $query->with(['shift:id,title', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->where('created_by', $auth_id)
            ->orderBy('date', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('shift', function ($q2) use ($search) {
                    $q2->where('title', 'LIKE', "%{$search}%");
                })->orWhere('late_reason', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'shift_id' => $item->shift_id,
                'shift_title' => optional($item->shift)->title ?? '',
                'date' => $item->date,
                'clock_in' => $item->clock_in,
                'clock_out' => $item->clock_out,
                'total_worked_hours' => $item->total_worked_hours,
                'is_late' => $item->is_late,
                'late_minutes' => $item->late_minutes,
                'late_reason' => $item->late_reason,
                'is_early_departure' => $item->is_early_departure,
                'early_departure_minutes' => $item->early_departure_minutes,
                'adjustment_request_status' => $item->adjustment_request_status,
                'late_deduction_applied' => $item->late_deduction_applied,
                'created_by_user' => optional($item->createdByUser)->name ?? '',
                'updated_by_user' => optional($item->updatedByUser)->name ?? '',
                'deleted_by_user' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $records = $query->get();
            $csvHeader = ['ID', 'Shift', 'Date', 'Clock In', 'Clock Out', 'Worked Hours', 'Late', 'Late Minutes', 'Late Reason', 'Early Departure', 'Early Minutes', 'Adjustment Request', 'Deduction', 'Created By', 'Created At', 'Updated By', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $item) {
                    $formatted = $format($item);
                    $row = [
                        $formatted['id'],
                        $formatted['shift_title'],
                        $formatted['date'],
                        $formatted['clock_in'],
                        $formatted['clock_out'],
                        $formatted['total_worked_hours'],
                        $formatted['is_late'] ? 'Yes' : 'No',
                        $formatted['late_minutes'],
                        $formatted['late_reason'],
                        $formatted['is_early_departure'] ? 'Yes' : 'No',
                        $formatted['early_departure_minutes'],
                        $formatted['adjustment_request_status'],
                        $formatted['late_deduction_applied'] ? 'Yes' : 'No',
                        $formatted['created_by_user'],
                        $formatted['created_at'],
                        $formatted['updated_by_user'],
                        $formatted['updated_at'],
                    ];

                    if ($is_deleted) {
                        $row[] = $formatted['deleted_by_user'];
                        $row[] = $formatted['deleted_at'];
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=my_attendances_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $records = $query->paginate($per_page);
        $formattedList = $records->map($format);

        return response()->json([
            'status' => true,
            'message' => 'My attendances list fetched.',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ],
            ],
        ]);
    }

    public function editAttendance($id)
    {
        $record = Attendance::withTrashed()
            ->with(['user:id,name', 'shift:id,title', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Attendance not found.'], 404);
        }

        $formatted = [
            'id' => $record->id,
            'user_id' => $record->user_id,
            'user_name' => optional($record->user)->name ?? '',
            'shift_id' => $record->shift_id,
            'shift_title' => optional($record->shift)->title ?? '',
            'date' => $record->date,
            'clock_in' => $record->clock_in,
            'clock_out' => $record->clock_out,
            'total_worked_hours' => $record->total_worked_hours,
            'is_late' => $record->is_late,
            'late_minutes' => $record->late_minutes,
            'late_reason' => $record->late_reason,
            'is_early_departure' => $record->is_early_departure,
            'early_departure_minutes' => $record->early_departure_minutes,
            'adjustment_request_status' => $record->adjustment_request_status,
            'late_deduction_applied' => $record->late_deduction_applied,
            'created_by_user' => optional($record->createdByUser)->name ?? '',
            'updated_by_user' => optional($record->updatedByUser)->name ?? '',
            'deleted_by_user' => optional($record->deletedByUser)->name ?? '',
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
            'deleted_at' => $record->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Attendance record fetched.',
            'data' => $formatted
        ]);
    }

    public function updateAttendance(Request $request, $id)
    {
        // Find attendance record or fail with 404
        $attendance = Attendance::findOrFail($id);

        // Validate input data
        $request->validate([
            'clock_in' => 'nullable|date_format:H:i:s',
            'clock_out' => 'nullable|date_format:H:i:s',
            'is_late' => 'boolean',
            'late_minutes' => 'nullable|integer',
            'late_reason' => 'nullable|string',
            'is_early_departure' => 'boolean',
            'early_departure_minutes' => 'nullable|integer',
            'adjustment_request_status' => 'nullable|string',
            'late_deduction_applied' => 'boolean',
        ]);

        // Update attendance fields selectively
        $attendance->fill($request->only([
            'clock_in', 'clock_out', 'is_late', 'late_minutes', 'late_reason',
            'is_early_departure', 'early_departure_minutes', 'adjustment_request_status', 'late_deduction_applied'
        ]));

        // Set who updated this record
        $attendance->updated_by = Auth::id();

        // Save changes to DB
        $attendance->save();

        // Return JSON response
        return response()->json([
            'status' => true,
            'message' => 'Attendance updated successfully.',
            'data' => $attendance,
        ]);
    }

    public function deleteAttendance($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json(['status' => false, 'message' => 'Attendance not found.'], 404);
        }

        $attendance->deleted_by = Auth::id();
        $attendance->save();
        $attendance->delete();

        return response()->json([
            'status' => true,
            'message' => 'Attendance deleted successfully.',
        ]);
    }

    public function revokeAttendance($id)
    {
        $attendance = Attendance::onlyTrashed()->find($id);

        if (!$attendance) {
            return response()->json(['status' => false, 'message' => 'Attendance not found.'], 404);
        }

        $attendance->restore();
        $attendance->deleted_by = null;
        $attendance->save();

        return response()->json([
            'status' => true,
            'message' => 'Attendance restored successfully.',
        ]);
    }
    
    public function sheetAttendance()
    {
        $from_date = request()->query('from_date'); // e.g., 2025-06-26
        $to_date = request()->query('to_date');     // e.g., 2025-07-25
        $user_ids = request()->query('user_ids');   // e.g., "1,3"

        $dates = CarbonPeriod::create($from_date, $to_date)->toArray();
        $dateHeaders = array_map(function ($d) {
            return $d->format('d M');
        }, $dates);

        // Fetch users
        $users = User::when($user_ids, function ($q) use ($user_ids) {
            return $q->whereIn('id', explode(',', $user_ids));
        })
        ->select('id', 'name')
        ->get();

        // Fetch shift data by user
        $shifts = DB::table('shift')
            ->whereNull('deleted_at')
            ->when($user_ids, function ($q) use ($user_ids) {
                return $q->whereIn('user_id', explode(',', $user_ids));
            })
            ->get()
            ->groupBy('user_id');

        // Attendance records grouped by user_id_date
        $attendances = Attendance::whereBetween('date', [$from_date, $to_date])
            ->when($user_ids, function ($q) use ($user_ids) {
                return $q->whereIn('user_id', explode(',', $user_ids));
            })
            ->get()
            ->groupBy(function ($a) {
                return $a->user_id . '_' . $a->date;
            });

        // Leave requests overlapping the date range
        $leaveQuery = LeaveRequest::where(function ($q) use ($from_date, $to_date) {
            $q->whereBetween('start_date', [$from_date, $to_date])
                ->orWhereBetween('end_date', [$from_date, $to_date])
                ->orWhere(function ($q2) use ($from_date, $to_date) {
                    $q2->where('start_date', '<=', $from_date)
                        ->where('end_date', '>=', $to_date);
                });
        });

        if ($user_ids) {
            $leaveQuery->whereIn('user_id', explode(',', $user_ids));
        }

        $leaves = $leaveQuery->get();

        // Map leave types to short codes
        $leaveTypeCodes = [
            'Annual' => 'AL',
            'Sick' => 'SL',
            'Parental' => 'PL',
            'Compensatory' => 'CL',
        ];

        // Build leave map: [user_id][date] = short_code
        $leavesByUserDate = [];
        foreach ($leaves as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $day) {
                $dayKey = $day->format('Y-m-d');
                $type = trim($leave->leave_type);
                $code = isset($leaveTypeCodes[$type]) ? $leaveTypeCodes[$type] : 'L';
                $leavesByUserDate[$leave->user_id][$dayKey] = $code;
            }
        }

        $data = [];

        // Header row
        $header = array_merge(['Employee Name'], $dateHeaders, ['Total Late', 'Total Off']);
        $data[] = $header;

        foreach ($users as $user) {
            $row = [$user->name];
            $lateCount = 0;
            $offCount = 0;

            foreach ($dates as $date) {
                $dateKey = $date->format('Y-m-d');
                $attendanceKey = $user->id . '_' . $dateKey;

                $isSunday = $date->isSunday();
                $isSaturday = $date->isSaturday();
                $userShift = isset($shifts[$user->id][0]) ? $shifts[$user->id][0] : null;

                $markHoliday = false;

                // Sunday is always holiday
                if ($isSunday) {
                    $markHoliday = true;
                }

                // Saturday logic
                if ($isSaturday && $userShift && $userShift->alternate_saturday_off == 1) {
                    $weekOfMonth = ceil($date->day / 7); // 1 to 5
                    if (in_array($weekOfMonth, [2, 4])) {
                        $markHoliday = true;
                    }
                }

                if ($markHoliday) {
                    $row[] = 'H';
                } elseif (isset($leavesByUserDate[$user->id][$dateKey])) {
                    $row[] = $leavesByUserDate[$user->id][$dateKey];
                } elseif ($attendances->has($attendanceKey)) {
                    $attendance = $attendances[$attendanceKey]->first();
                    $row[] = Carbon::parse($attendance->clock_in)->format('H:i');
                    if ($attendance->is_late) {
                        $lateCount++;
                    }
                } else {
                    $row[] = 'A';
                    $offCount++;
                }
            }

            $row[] = $lateCount;
            $row[] = $offCount;
            $data[] = $row;
        }

        return response()->json([
            'status' => true,
            'message' => 'Attendance sheet fetched.',
            'data' => $data,
        ]);
    }

}