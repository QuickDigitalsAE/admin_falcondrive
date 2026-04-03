<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Department_ViewAll', ['only' => ['getDepartments']]);
        $this->middleware('permission:Department_ViewMine', ['only' => ['getMyDepartments']]);
        $this->middleware('permission:Department_View', ['only' => ['editDepartment']]);
        $this->middleware('permission:Department_Add', ['only' => ['postDepartment']]);
        $this->middleware('permission:Department_Edit', ['only' => ['updateDepartment']]);
        $this->middleware('permission:Department_Delete', ['only' => ['deleteDepartment']]);
        $this->middleware('permission:Department_Revoke', ['only' => ['revokeDepartment']]);
    }

    // POST /department — Create new department
    public function postDepartment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255|unique:department,title,NULL,id,deleted_at,NULL',
            'desc' => 'nullable|string',
        ]);

        $department = Department::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Department created successfully!',
            'data' => $department,
        ], Response::HTTP_CREATED);
    }

    // GET /department — List departments (with optional export, deleted, search, pagination)
    public function getDepartments(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted ? Department::onlyTrashed() : Department::query();
        $query->with([
                'manager:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
            ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('desc', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'desc' => $item->desc,

                'user_id' => $item->user_id,
                'user_name' => optional($item->manager)->name ?? null,

                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at
            ];
        };

        if ($is_export) {
            $departments = $query->get();
            $csvHeader = ['ID', 'Manager Name', 'Title', 'Description', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($departments, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($departments as $dept) {
                    $f = $format($dept);
                    $row = [
                        $f['id'], $f['user_name'], $f['title'], $f['desc'],
                        $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=departments_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $departments = $query->paginate($per_page);
        $formattedList = $departments->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Department list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'per_page' => $departments->perPage(),
                    'current_page' => $departments->currentPage(),
                    'last_page' => $departments->lastPage(),
                    'total' => $departments->total(),
                ],
            ],
        ]);
    }

    public function getMyDepartments(Request $request)
    {
        $user = auth()->user();
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted
            ? Department::onlyTrashed()->where('created_by', $user->id)
            : Department::where('created_by', $user->id);

        $query->with([
                'manager:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('desc', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'desc' => $item->desc,

                'user_id' => $item->user_id,
                'user_name' => optional($item->manager)->name ?? null,

                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at
            ];
        };

        if ($is_export) {
            $departments = $query->get();
            $csvHeader = ['ID', 'Manager Name', 'Title', 'Description', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($departments, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($departments as $dept) {
                    $f = $format($dept);
                    $row = [
                        $f['id'], $f['user_name'], $f['title'], $f['desc'],
                        $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=my_departments_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $departments = $query->paginate($per_page);
        $formattedList = $departments->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My department list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'per_page' => $departments->perPage(),
                    'current_page' => $departments->currentPage(),
                    'last_page' => $departments->lastPage(),
                    'total' => $departments->total(),
                ],
            ],
        ]);
    }

    // GET /department/{id} — Show department detail
    public function editDepartment($id)
    {
        $department = Department::withTrashed()
            ->with([
                'manager:id,name',
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
            ])->findOrFail($id);

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->manager)->name ?? '',
                'title' => $item->title,
                'desc' => $item->desc,

                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at
            ];
        };

        return response()->json([
            'status' => true,
            'message' => 'Department detail fetched successfully!',
            'data' => $format($department),
        ]);
    }

    // PUT /department/{id} — Update department
    public function updateDepartment(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255|unique:department,title,' . $id . ',id,deleted_at,NULL',
            'desc' => 'nullable|string',
        ]);

        $department->update([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Department updated successfully!',
            'data' => $department,
        ]);
    }

    // DELETE /department/{id} — Soft delete department
    public function deleteDepartment($id)
    {
        $department = Department::findOrFail($id);
        $department->update(['deleted_by' => Auth::id()]);
        $department->delete();

        return response()->json([
            'status' => true,
            'message' => 'Department deleted successfully!',
        ]);
    }

    // PUT /department/revoke/{id} — Restore soft-deleted department
    public function revokeDepartment($id)
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();
        $onboarding->deleted_by = null;
        $onboarding->save();

        return response()->json([
            'status' => true,
            'message' => 'Department restored successfully!',
        ]);
    }
}
