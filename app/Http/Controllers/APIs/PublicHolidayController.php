<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ImageUrlTrait;
use App\Models\PublicHoliday;
use Illuminate\Support\Facades\Auth;

class PublicHolidayController extends Controller
{
    use ImageUrlTrait;
    
    public function __construct()
    {
        $this->middleware('permission:PublicHoliday_ViewAll', ['only' => ['getPublicHolidays']]);
        $this->middleware('permission:PublicHoliday_ViewMine', ['only' => ['getMyPublicHolidays']]);
        $this->middleware('permission:PublicHoliday_View', ['only' => ['editPublicHoliday']]);
        $this->middleware('permission:PublicHoliday_Add', ['only' => ['postPublicHoliday']]);
        $this->middleware('permission:PublicHoliday_Edit', ['only' => ['updatePublicHoliday']]);
        $this->middleware('permission:PublicHoliday_Delete', ['only' => ['deletePublicHoliday']]);
        $this->middleware('permission:PublicHoliday_Revoke', ['only' => ['revokePublicHoliday']]);
    }

    public function getPublicHolidays()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted ? PublicHoliday::onlyTrashed() : PublicHoliday::query();
        $query->orderBy('date', 'ASC');

        if (!empty($search)) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if ($is_export) {
            $holidays = $query->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->get();

            $csvHeader = ['ID', 'Title', 'Date', 'Type', 'Category', 'Compensatory Off Allowed', 'Year', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($holidays, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($holidays as $h) {
                    $row = [
                        $h->id,
                        $h->title,
                        $h->date,
                        $h->type,
                        $h->category,
                        $h->compensatory_off_allowed,
                        $h->year,
                        optional($h->createdByUser)->name ?? 'N/A',
                        optional($h->updatedByUser)->name ?? 'N/A',
                        $h->created_at,
                        $h->updated_at,
                    ];

                    if ($is_deleted) {
                        $row[] = optional($h->deletedByUser)->name ?? 'N/A';
                        $row[] = $h->deleted_at;
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            $fileName = 'public_holidays_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        $holidays = $query->paginate($per_page);
        if ($holidays->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Public holiday not found.', 'data' => []], 404);
        }

        $list = $holidays->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'date' => $c->date,
                'compensatory_off_allowed' => $c->compensatory_off_allowed,
                'type' => $c->type,
                'category' => $c->category,
                'year' => $c->year,
                'created_by' => optional($c->createdByUser)->name,
                'updated_by' => optional($c->updatedByUser)->name,
                'deleted_by' => optional($c->deletedByUser)->name,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
                'deleted_at' => $c->deleted_at
            ];
        });

        $pagination = [
            'current_page' => $holidays->currentPage(),
            'last_page' => $holidays->lastPage(),
            'per_page' => $holidays->perPage(),
            'total' => $holidays->total(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Public holidays fetched successfully.',
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ],
        ], 200);
    }

    public function getMyPublicHolidays()
    {
        $authId = Auth::id();
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted 
            ? PublicHoliday::onlyTrashed()->where('created_by', $authId)
            : PublicHoliday::where('created_by', $authId);

        $query->orderBy('date', 'ASC');

        if (!empty($search)) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if ($is_export) {
            $holidays = $query->with(['createdByUser', 'updatedByUser', 'deletedByUser'])->get();

            $csvHeader = ['ID', 'Title', 'Date', 'Type', 'Category', 'Compensatory Off Allowed', 'Year', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($holidays, $csvHeader, $is_deleted) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($holidays as $h) {
                    $row = [
                        $h->id,
                        $h->title,
                        $h->date,
                        $h->type,
                        $h->category,
                        $h->compensatory_off_allowed,
                        $h->year,
                        optional($h->createdByUser)->name ?? 'N/A',
                        optional($h->updatedByUser)->name ?? 'N/A',
                        $h->created_at,
                        $h->updated_at,
                    ];

                    if ($is_deleted) {
                        $row[] = optional($h->deletedByUser)->name ?? 'N/A';
                        $row[] = $h->deleted_at;
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            $fileName = 'my_public_holidays_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        $holidays = $query->paginate($per_page);

        if ($holidays->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No public holidays found.',
                'data' => [],
            ], 404);
        }

        $list = $holidays->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'date' => $c->date,
                'compensatory_off_allowed' => $c->compensatory_off_allowed,
                'type' => $c->type,
                'category' => $c->category,
                'year' => $c->year,
                'created_by' => optional($c->createdByUser)->name,
                'updated_by' => optional($c->updatedByUser)->name,
                'deleted_by' => optional($c->deletedByUser)->name,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
                'deleted_at' => $c->deleted_at
            ];
        });

        $pagination = [
            'current_page' => $holidays->currentPage(),
            'last_page' => $holidays->lastPage(),
            'per_page' => $holidays->perPage(),
            'total' => $holidays->total(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'My public holidays fetched successfully.',
            'data' => ['list' => $list, 'pagination' => $pagination],
        ], 200);
    }

    public function postPublicHoliday(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'compensatory_off_allowed' => 'required|string|max:191',
            'type' => 'required|string|max:191',
            'category' => 'nullable|string|max:191',
            'year' => 'required|integer',
        ]);

        $holiday = PublicHoliday::create([
            'title' => $request->title,
            'date' => $request->date,
            'compensatory_off_allowed' => $request->compensatory_off_allowed,
            'type' => $request->type,
            'category' => $request->category,
            'year' => $request->year,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Public holiday created successfully.',
            'data' => $holiday,
        ], Response::HTTP_CREATED);
    }

    public function editPublicHoliday($id)
    {
        $holiday = PublicHoliday::with([
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
            'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
        ])->withTrashed()->find($id);

        if (!$holiday) {
            return response()->json([
                'status' => false,
                'message' => 'Public holiday not found.',
            ], 404);
        }

        $data = [
            'id' => $holiday->id,
            'title' => $holiday->title,
            'date' => $holiday->date ? $holiday->date : null,
            'type' => $holiday->type,
            'category' => $holiday->category,
            'year' => $holiday->year,
            'compensatory_off_allowed' => $holiday->compensatory_off_allowed,

            'attachments' => collect($holiday->attachments)->map(function ($att) {
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

            'created_by' => optional($holiday->createdByUser)->name,
            'updated_by' => optional($holiday->updatedByUser)->name,
            'deleted_by' => optional($holiday->deletedByUser)->name,

            'created_at' => $holiday->created_at,
            'updated_at' => $holiday->updated_at,
            'deleted_at' => $holiday->deleted_at,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Public holiday fetched successfully.',
            'data' => $data,
        ]);
    }

    public function updatePublicHoliday(Request $request, $id)
    {
        $holiday = PublicHoliday::withTrashed()->find($id);

        if (!$holiday) {
            return response()->json(['status' => false, 'message' => 'Public holiday not found.'], 404);
        }

        $holiday->fill($request->only([
            'title', 'date', 'compensatory_off_allowed', 'type', 'category', 'year'
        ]));
        $holiday->updated_by = Auth::id();
        $holiday->save();

        return response()->json([
            'status' => true,
            'message' => 'Public holiday updated successfully.',
            'data' => $holiday,
        ], 200);
    }

    public function deletePublicHoliday($id)
    {
        $holiday = PublicHoliday::find($id);

        if (!$holiday) {
            return response()->json(['status' => false, 'message' => 'Public holiday not found.'], 404);
        }

        $holiday->deleted_by = Auth::id();
        $holiday->save();
        $holiday->delete();

        return response()->json([
            'status' => true,
            'message' => 'Public holiday deleted successfully.',
        ], 200);
    }

    public function revokePublicHoliday($id)
    {
        $holiday = PublicHoliday::onlyTrashed()->find($id);

        if (!$holiday) {
            return response()->json(['status' => false, 'message' => 'Public holiday not found.'], 404);
        }

        $holiday->restore();
        $holiday->deleted_by = null;
        $holiday->save();

        return response()->json([
            'status' => true,
            'message' => 'Public holiday restored successfully.',
        ], 200);
    }
}
