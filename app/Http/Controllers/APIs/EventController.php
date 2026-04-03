<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
     public function __construct()
    {
        $this->middleware('permission:Event_ViewAll', ['only' => ['getEvents']]);
        $this->middleware('permission:Event_ViewMine', ['only' => ['getMyEvents']]);
        $this->middleware('permission:Event_View', ['only' => ['editEvent']]);
        $this->middleware('permission:Event_Add', ['only' => ['postEvent']]);
        $this->middleware('permission:Event_Edit', ['only' => ['updateEvent']]);
        $this->middleware('permission:Event_Delete', ['only' => ['deleteEvent']]);
        $this->middleware('permission:Event_Revoke', ['only' => ['revokeEvent']]);
    }

    public function postEvent(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'start_event' => 'required|date',
            'end_event' => 'required|date|after_or_equal:start_event',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'start_event' => $request->start_event,
            'end_event' => $request->end_event,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Event created successfully!',
            'data' => $event,
        ], Response::HTTP_CREATED);
    }

    public function getEvents(Request $request)
    {
        $search    = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport  = $request->query('is_export');
        $startDate = $request->query('start_date'); // expected format: '2025-07-01 00:00:00' or '2025-07-01'
        $endDate   = $request->query('end_date');   // expected format: '2025-07-31 23:59:59' or '2025-07-31'

        $query = $isDeleted ? Event::onlyTrashed() : Event::query();

        $query->with([
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_event', '<=', $endDate)
                ->where('end_event', '>=', $startDate);
            });
        } elseif (!empty($startDate)) {
            $query->where('end_event', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->where('start_event', '<=', $endDate);
        }

        $format = function ($item) {
            return [
                'id'          => $item->id,
                'title'       => $item->title,
                'start_event' => $item->start_event,
                'end_event'   => $item->end_event,
                'created_by'  => optional($item->createdByUser)->name ?? '',
                'updated_by'  => optional($item->updatedByUser)->name ?? '',
                'deleted_by'  => optional($item->deletedByUser)->name ?? '',
                'created_at'  => $item->created_at,
                'updated_at'  => $item->updated_at,
                'deleted_at'  => $item->deleted_at,
            ];
        };

        // Export CSV
        if ($isExport) {
            $events = $query->get();
            $csvHeader = ['ID', 'Title', 'Start', 'End', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($events, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($events as $event) {
                    $f = $format($event);
                    $row = [
                        $f['id'], $f['title'], $f['start_event'], $f['end_event'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at'],
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
                'Content-Disposition' => 'attachment; filename=events_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        // Get all records (no pagination)
        $events = $query->get();
        $formatted = $events->map($format);

        return response()->json([
            'status'  => true,
            'message' => 'Event list fetched successfully!',
            'data'    => [
                'list' => $formatted,
            ],
        ]);
    }


    public function getMyEvents(Request $request)
    {
        $userId = auth()->id();
        $search = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');
        $startDate = $request->query('start_date'); // expected format: '2025-07-01 00:00:00' or '2025-07-01'
        $endDate   = $request->query('end_date');   // expected format: '2025-07-31 23:59:59' or '2025-07-31'

        $query = $isDeleted
            ? Event::onlyTrashed()->where('created_by', $userId)
            : Event::where('created_by', $userId);

        $query->with([
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_event', '<=', $endDate)
                ->where('end_event', '>=', $startDate);
            });
        } elseif (!empty($startDate)) {
            $query->where('end_event', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->where('start_event', '<=', $endDate);
        }

        $format = function ($item) {
            return [
                'id'          => $item->id,
                'title'       => $item->title,
                'start_event' => $item->start_event,
                'end_event'   => $item->end_event,
                'created_by'  => optional($item->createdByUser)->name ?? '',
                'updated_by'  => optional($item->updatedByUser)->name ?? '',
                'deleted_by'  => optional($item->deletedByUser)->name ?? '',
                'created_at'  => $item->created_at,
                'updated_at'  => $item->updated_at,
                'deleted_at'  => $item->deleted_at,
            ];
        };

        // Export CSV
        if ($isExport) {
            $events = $query->get();
            $csvHeader = ['ID', 'Title', 'Start', 'End', 'Created By', 'Updated By', 'Created At', 'Updated At'];
            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($events, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);
                foreach ($events as $event) {
                    $f = $format($event);
                    $row = [
                        $f['id'], $f['title'], $f['start_event'], $f['end_event'],
                        $f['created_by'], $f['updated_by'], $f['created_at'], $f['updated_at'],
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
                'Content-Disposition' => 'attachment; filename=events_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        // Get all records (no pagination)
        $events = $query->get();
        $formatted = $events->map($format);

        return response()->json([
            'status'  => true,
            'message' => 'Event list fetched successfully!',
            'data'    => [
                'list' => $formatted,
            ],
        ]);
    }

    public function editEvent($id)
    {
        $event = Event::withTrashed()
            ->with([
                'createdByUser:id,name',
                'updatedByUser:id,name',
                'deletedByUser:id,name',
            ])->findOrFail($id);

        $format = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'start_event' => $item->start_event,
                'end_event' => $item->end_event,

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
            'message' => 'Event detail fetched successfully!',
            'data' => $format($event),
        ]);
    }
    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'title' => 'required|string',
            'start_event' => 'required|date',
            'end_event' => 'required|date|after_or_equal:start_event',
        ]);

        $event->update([
            'title' => $request->title,
            'start_event' => $request->start_event,
            'end_event' => $request->end_event,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Event updated successfully!',
            'data' => $event,
        ]);
    }

    public function deleteEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->update(['deleted_by' => Auth::id()]);
        $event->delete();

        return response()->json([
            'status' => true,
            'message' => 'Event deleted successfully!',
        ]);
    }

    public function revokeEvent($id)
    {
        $event = Event::onlyTrashed()->findOrFail($id);
        $event->restore();
        $event->update(['deleted_by' => null]);

        return response()->json([
            'status' => true,
            'message' => 'Event restored successfully!',
        ]);
    }
}
