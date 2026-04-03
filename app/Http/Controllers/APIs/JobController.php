<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:Job_ViewAll', ['only' => ['getJobs']]);
        $this->middleware('permission:Job_ViewMine', ['only' => ['getMyJobs']]);
        $this->middleware('permission:Job_View', ['only' => ['editJob']]);
        $this->middleware('permission:Job_Add', ['only' => ['postJobt']]);
        $this->middleware('permission:Job_Edit', ['only' => ['updateJob']]);
        $this->middleware('permission:Job_Delete', ['only' => ['deleteJob']]);
        $this->middleware('permission:Job_Revoke', ['only' => ['revokeJob']]);
    }

    public function postJob(Request $request)
    {
        $validator = [
            'title'         => 'required|string',
            'desc'          => 'nullable|string',
            'body'          => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'position'      => 'required|string',
            'department_id' => 'required|exists:department,id',
            'quantity'      => 'nullable|integer',
        ];

        $request->validate($validator);

        $auth = Auth::user();

        $job = Job::create([
            'title'         => $request->title,
            'desc'          => $request->desc,
            'body'          => $request->body,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'position'      => $request->position,
            'department_id' => $request->department_id,
            'quantity'      => $request->quantity ?? 0,
            'created_by'    => $auth->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Job added successfully.',
            'data' => [
                'id'            => $job->id,
                'title'         => $job->title,
                'desc'          => $job->desc,
                'body'          => $job->body,
                'start_date'    => $job->start_date,
                'end_date'      => $job->end_date,
                'position'      => $job->position,
                'department_id' => $job->department_id,
                'quantity'      => $job->quantity,
            ]
        ], Response::HTTP_CREATED);
    }

    public function getJobs()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted ? Job::onlyTrashed() : Job::query();
        $query->with(['department:id,title', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('desc', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('position', 'LIKE', "%{$search}%")
                ->orWhereHas('department', function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'desc' => $job->desc,
                'body' => $job->body,
                'start_date' => $job->start_date,
                'end_date' => $job->end_date,
                'position' => $job->position,
                'department_id' => $job->department_id,
                'department_name' => optional($job->department)->title,
                'quantity' => $job->quantity,
                
                'created_by' => optional($job->createdByUser)->name,
                'updated_by' => optional($job->updatedByUser)->name,
                'deleted_by' => optional($job->deletedByUser)->name,
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
                'deleted_at' => $job->deleted_at,
            ];
        };

        if ($is_export) {
            $jobs = $query->get();
            $csvHeader = ['ID', 'Title', 'Desc', 'Body', 'Start Date', 'End Date', 'Position', 'Department', 'Quantity', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($jobs, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($jobs as $job) {
                    $f = $format($job);
                    $row = [
                        $f['id'], $f['title'], $f['desc'], $f['body'],
                        $f['start_date'], $f['end_date'], $f['position'], $f['department_name'],
                        $f['quantity'], $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=jobs_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $jobs = $query->paginate($per_page);
        $formattedList = $jobs->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Job list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $jobs->total(),
                    'per_page' => $jobs->perPage(),
                    'current_page' => $jobs->currentPage(),
                    'last_page' => $jobs->lastPage(),
                ],
            ],
        ]);
    }

    public function getMyJobs()
    {
        $user = auth()->user();
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted
            ? Job::onlyTrashed()->where('created_by', $user->id)
            : Job::where('created_by', $user->id);

        $query->with(['department:id,title', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('desc', 'LIKE', "%{$search}%")
                ->orWhere('body', 'LIKE', "%{$search}%")
                ->orWhere('position', 'LIKE', "%{$search}%")
                ->orWhereHas('department', function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%");
                });
            });
        }

        $format = function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'desc' => $job->desc,
                'body' => $job->body,
                'start_date' => $job->start_date,
                'end_date' => $job->end_date,
                'position' => $job->position,
                'department_id' => $job->department_id,
                'department_name' => optional($job->department)->title,
                'quantity' => $job->quantity,
                
                'created_by' => optional($job->createdByUser)->name,
                'updated_by' => optional($job->updatedByUser)->name,
                'deleted_by' => optional($job->deletedByUser)->name,
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
                'deleted_at' => $job->deleted_at,
            ];
        };

        if ($is_export) {
            $jobs = $query->get();
            $csvHeader = ['ID', 'Title', 'Desc', 'Body', 'Start Date', 'End Date', 'Position', 'Department', 'Quantity', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($jobs, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($jobs as $job) {
                    $f = $format($job);
                    $row = [
                        $f['id'], $f['title'], $f['desc'], $f['body'],
                        $f['start_date'], $f['end_date'], $f['position'], $f['department_name'],
                        $f['quantity'], $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=my_jobs_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $jobs = $query->paginate($per_page);
        $formattedList = $jobs->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My job list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $jobs->total(),
                    'per_page' => $jobs->perPage(),
                    'current_page' => $jobs->currentPage(),
                    'last_page' => $jobs->lastPage(),
                ],
            ],
        ]);
    }

    public function editJob($id)
    {
        $job = Job::withTrashed()
            ->with(['department:id,title', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found!',
                'data' => []
            ], 404);
        }

        $data = [
            'id' => $job->id,
            'title' => $job->title,
            'desc' => $job->desc,
            'body' => $job->body,
            'start_date' => $job->start_date,
            'end_date' => $job->end_date,
            'position' => $job->position,
            'department_id' => $job->department_id,
            'department_name' => optional($job->department)->title,
            'quantity' => $job->quantity,
            
            'created_by' => optional($job->createdByUser)->name,
            'updated_by' => optional($job->updatedByUser)->name,
            'deleted_by' => optional($job->deletedByUser)->name,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
            'deleted_at' => $job->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Job details fetched successfully!',
            'data' => $data,
        ]);
    }

    public function updateJob(Request $request, $id)
    {
        $validator = [
            'title'         => 'required|string',
            'desc'          => 'nullable|string',
            'body'          => 'nullable|string',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date',
            'position'      => 'nullable|string',
            'department_id' => 'nullable|exists:department,id',
            'quantity'      => 'nullable|integer',
        ];

        $request->validate($validator);

        $job = Job::withTrashed()->findOrFail($id);
        $authId = auth()->id();

        $job->update([
            'title'         => $request->title,
            'desc'          => $request->desc,
            'body'          => $request->body,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'position'      => $request->position,
            'department_id' => $request->department_id,
            'quantity'      => $request->quantity ?? 0,
            'updated_by'    => $authId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Job updated successfully.',
            'data' => [
                'id'            => $job->id,
                'title'         => $job->title,
                'desc'          => $job->desc,
                'body'          => $job->body,
                'start_date'    => $job->start_date,
                'end_date'      => $job->end_date,
                'position'      => $job->position,
                'department_id' => $job->department_id,
                'quantity'      => $job->quantity,
            ]
        ], Response::HTTP_OK);
    }

    public function deleteJob($id)
    {
        $authId = Auth::id();
        $job = Job::find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        $job->deleted_by = $authId;
        $job->save();
        $job->delete();

        return response()->json([
            'status' => true,
            'message' => 'Job deleted successfully.',
            'data' => []
        ], Response::HTTP_OK);
    }

    public function revokeJob($id)
    {
        $job = Job::withTrashed()->find($id);

        if (!$job) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        if (is_null($job->deleted_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Job is not deleted.',
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }

        $job->restore();
        $job->deleted_by = null;
        $job->save();

        return response()->json([
            'status' => true,
            'message' => 'Job has been successfully restored.',
            'data' => []
        ], Response::HTTP_OK);
    }
}
