<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Attachment;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;

class AttachmentController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:Attachment_ViewAll', ['only' => ['getAttachments']]);
        $this->middleware('permission:Attachment_ViewMine', ['only' => ['getMyAttachments']]);
        $this->middleware('permission:Attachment_Add', ['only' => ['postAttachment']]);
        $this->middleware('permission:Attachment_Edit', ['only' => ['updateAttachment']]);
        $this->middleware('permission:Attachment_Delete', ['only' => ['deleteAttachment']]);
        $this->middleware('permission:Attachment_Revoke', ['only' => ['revokeAttachment']]);
    }

    public function postAttachment(Request $request)
    {
        $request->validate([
            'table_name' => 'required|string',
            'table_primary_key' => 'required|string',
            'type' => 'nullable|string',
            'category' => 'nullable|string',
            'title' => 'nullable|string',
            'desc' => 'nullable|string',
            'url' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $attachment = Attachment::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Attachment created.',
            'data' => $attachment
        ], Response::HTTP_CREATED);
    }

    public function getAttachments()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');

        $query = $is_deleted ? Attachment::onlyTrashed() : Attachment::query();
        $query->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('table_name', 'LIKE', "%{$search}%")
                ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'table_name' => $item->table_name,
                'table_primary_key' => $item->table_primary_key,
                'type' => $item->type,
                'category' => $item->category,
                'title' => $item->title,
                'desc' => $item->desc,
                'url' => $item->url ? $this->getImageUrl($item->url) : null,

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

            $csvHeader = ['ID', 'Table', 'Primary Key', 'Type', 'Category', 'Title', 'Description', 'URL', 'Created By', 'Created At', 'Updated By', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $r) {
                    $formatted = $format($r);

                    $row = [
                        $formatted['id'],
                        $formatted['table_name'],
                        $formatted['table_primary_key'],
                        $formatted['type'],
                        $formatted['category'],
                        $formatted['title'],
                        $formatted['desc'],
                        $formatted['url'],
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
                'Content-Disposition' => 'attachment; filename=attachments_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $attachments = $query->paginate($per_page);
        
        $formattedList = $attachments->map($format);

        return response()->json([
            'status' => true,
            'message' => 'Attachments list fetched.',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $attachments->currentPage(),
                    'last_page' => $attachments->lastPage(),
                    'per_page' => $attachments->perPage(),
                    'total' => $attachments->total(),
                ],
            ],
        ]);
    }

    public function getMyAttachments()
    {
        $per_page = getPerPage();
        $search = request()->query('search');
        $is_deleted = request()->query('is_deleted');
        $is_export = request()->query('is_export');
        $auth_id = auth()->id();

        $query = $is_deleted ? Attachment::onlyTrashed() : Attachment::query();
        $query->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->where('created_by', $auth_id)
            ->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('table_name', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'table_name' => $item->table_name,
                'table_primary_key' => $item->table_primary_key,
                'type' => $item->type,
                'category' => $item->category,
                'title' => $item->title,
                'desc' => $item->desc,
                'url' => $item->url ? $this->getImageUrl($item->url) : null,

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

            $csvHeader = ['ID', 'Table', 'Primary Key', 'Type', 'Category', 'Title', 'Description', 'URL', 'Created By', 'Created At', 'Updated By', 'Updated At'];
            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $r) {
                    $formatted = $format($r);

                    $row = [
                        $formatted['id'],
                        $formatted['table_name'],
                        $formatted['table_primary_key'],
                        $formatted['type'],
                        $formatted['category'],
                        $formatted['title'],
                        $formatted['desc'],
                        $formatted['url'],
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
                'Content-Disposition' => 'attachment; filename=my_attachments_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $attachments = $query->paginate($per_page);
        $formattedList = $attachments->map($format);

        return response()->json([
            'status' => true,
            'message' => 'My attachments list fetched.',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'current_page' => $attachments->currentPage(),
                    'last_page' => $attachments->lastPage(),
                    'per_page' => $attachments->perPage(),
                    'total' => $attachments->total(),
                ],
            ],
        ]);
    }

    public function editAttachment($id)
    {
        $attachment = Attachment::withTrashed()
            ->with(['createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->find($id);

        if (!$attachment) {
            return response()->json(['status' => false, 'message' => 'Attachment not found.'], 404);
        }

        $formatted = (function ($item) {
            return [
                'id' => $item->id,
                'table_name' => $item->table_name,
                'table_primary_key' => $item->table_primary_key,
                'type' => $item->type,
                'category' => $item->category,
                'title' => $item->title,
                'desc' => $item->desc,
                'url' => $item->url,
                'full_url' => $item->url ? $this->getImageUrl($item->url) : null, 

                'created_by_user' => optional($item->createdByUser)->name ?? '',
                'updated_by_user' => optional($item->updatedByUser)->name ?? '',
                'deleted_by_user' => optional($item->deletedByUser)->name ?? '',

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at
            ];
        })($attachment);

        return response()->json([
            'status' => true,
            'message' => 'Attachment fetched.',
            'data' => $formatted,
        ]);
    }

    public function updateAttachment(Request $request, $id)
    {
        $attachment = Attachment::findOrFail($id);

        $request->validate([
            'table_name' => 'nullable|string',
            'table_primary_key' => 'nullable|string',
            'type' => 'nullable|string',
            'category' => 'nullable|string',
            'title' => 'nullable|string',
            'desc' => 'nullable|string',
            'url' => 'nullable|string',
        ]);

        $attachment->fill($request->only([
            'table_name',
            'table_primary_key',
            'type',
            'category',
            'title',
            'desc',
            'url',
        ]));

        $attachment->updated_by = Auth::id();
        $attachment->save();

        return response()->json([
            'status' => true,
            'message' => 'Attachment updated.',
            'data' => $attachment
        ]);
    }

    public function deleteAttachment($id)
    {
        $attachment = Attachment::find($id);

        if (!$attachment) {
            return response()->json(['status' => false, 'message' => 'Attachment not found.'], 404);
        }

        $attachment->deleted_by = Auth::id();
        $attachment->save();
        $attachment->delete();

        return response()->json(['status' => true, 'message' => 'Attachment deleted.']);
    }

    public function revokeAttachment($id)
    {
        $attachment = Attachment::onlyTrashed()->find($id);

        if (!$attachment) {
            return response()->json(['status' => false, 'message' => 'Attachment not found.'], 404);
        }

        $attachment->restore();
        $attachment->deleted_by = null;
        $attachment->save();

        return response()->json(['status' => true, 'message' => 'Attachment restored.']);
    }
}
