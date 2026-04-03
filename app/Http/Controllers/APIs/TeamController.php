<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Team;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Team_ViewAll', ['only' => ['getTeams']]);
        $this->middleware('permission:Team_ViewMine', ['only' => ['getMyTeams']]);
        $this->middleware('permission:Team_View', ['only' => ['editTeam']]);
        $this->middleware('permission:Team_Add', ['only' => ['postTeam']]);
        $this->middleware('permission:Team_Edit', ['only' => ['updateTeam']]);
        $this->middleware('permission:Team_Delete', ['only' => ['deleteTeam']]);
        $this->middleware('permission:Team_Revoke', ['only' => ['revokeTeam']]);
    }

    // POST /team — Create new team
    public function postTeam(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        // Check if the user already has a team
        $existingTeam = Team::withTrashed()
                            ->where('user_id', $request->user_id)
                            ->where('manager_id', $request->manager_id)
                            ->first();

        if ($existingTeam) {
            return response()->json([
                'status' => false,
                'message' => 'Employee already exists for this manager.',
            ], Response::HTTP_CONFLICT);
        }
        
        $team = Team::create([
            'user_id' => $request->user_id,
            'manager_id' => $request->manager_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Team created successfully!',
            'data' => $team,
        ], Response::HTTP_CREATED);
    }

    // GET /team — List teams (with optional export, deleted, search, pagination)
    public function getTeams(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted ? Team::onlyTrashed() : Team::query();
        $query->with(['user:id,name', 'manager:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('createdByUser', function ($q1) use ($search) {
                    $q1->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('manager', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'manager_id' => $item->manager_id,
                'manager_name' => optional($item->manager)->name ?? '',
                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $teams = $query->get();
            $csvHeader = ['ID', 'User', 'Manager', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($teams, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($teams as $team) {
                    $f = $format($team);
                    $row = [
                        $f['id'], $f['user_name'], $f['manager_name'], $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=teams_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $teams = $query->paginate($per_page);
        $formattedList = $teams->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'Team list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $teams->total(),
                    'per_page' => $teams->perPage(),
                    'current_page' => $teams->currentPage(),
                    'last_page' => $teams->lastPage(),
                ],
            ],
        ]);
    }

    // GET /team/my — Get teams created by the logged-in user
    public function getMyTeams(Request $request)
    {
        $user = auth()->user();
        $per_page = getPerPage();
        $search = $request->query('search');
        $is_deleted = $request->query('is_deleted');
        $is_export = $request->query('is_export');

        $query = $is_deleted
            ? Team::onlyTrashed()->where('created_by', $user->id)
            : Team::where('created_by', $user->id);

        $query->with(['user:id,name', 'manager:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('createdByUser', function ($q1) use ($search) {
                    $q1->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('manager', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        }

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'manager_id' => $item->manager_id,
                'manager_name' => optional($item->manager)->name ?? '',
                'created_by' => optional($item->createdByUser)->name ?? '',
                'updated_by' => optional($item->updatedByUser)->name ?? '',
                'deleted_by' => optional($item->deletedByUser)->name ?? '',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        if ($is_export) {
            $teams = $query->get();
            $csvHeader = ['ID', 'User', 'Manager', 'Created By', 'Updated By', 'Created At', 'Updated At'];

            if ($is_deleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($teams, $csvHeader, $is_deleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($teams as $team) {
                    $f = $format($team);
                    $row = [
                        $f['id'], $f['user_name'], $f['manager_name'], $f['created_by'], $f['updated_by'],
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
                'Content-Disposition' => 'attachment; filename=my_teams_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        $teams = $query->paginate($per_page);
        $formattedList = $teams->map($format)->values();

        return response()->json([
            'status' => true,
            'message' => 'My team list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $teams->total(),
                    'per_page' => $teams->perPage(),
                    'current_page' => $teams->currentPage(),
                    'last_page' => $teams->lastPage(),
                ],
            ],
        ]);
    }

    // GET /team/{id} — Show team detail
    public function editTeam($id)
    {
        $team = Team::withTrashed()
            ->with(['user:id,name', 'manager:id,name', 'createdByUser:id,name', 'updatedByUser:id,name', 'deletedByUser:id,name'])
            ->findOrFail($id);

        $format = function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'manager_id' => $item->manager_id,
                'manager_name' => optional($item->manager)->name ?? '',
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
            'message' => 'Team detail fetched successfully!',
            'data' => $format($team),
        ]);
    }

    // PUT /team/{id} — Update team
    public function updateTeam(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $existingTeam = Team::withTrashed()
            ->where('user_id', $request->user_id)
            ->where('manager_id', $request->manager_id)
            ->where('id', '!=', $id) // Exclude current team entry
            ->first();

        if ($existingTeam) {
            return response()->json([
                'status' => false,
                'message' => 'Employee already exists for this manager.',
            ], Response::HTTP_CONFLICT);
        }


        $team->update([
            'user_id' => $request->user_id,
            'manager_id' => $request->manager_id,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Team updated successfully!',
            'data' => $team,
        ]);
    }

    // DELETE /team/{id} — Soft delete team
    public function deleteTeam($id)
    {
        $team = Team::findOrFail($id);
        $team->update(['deleted_by' => Auth::id()]);
        $team->delete();

        return response()->json([
            'status' => true,
            'message' => 'Team deleted successfully!',
        ]);
    }

    // PUT /team/revoke/{id} — Restore soft-deleted team
    public function revokeTeam($id)
    {
        $team = Team::onlyTrashed()->findOrFail($id);
        $team->restore();
        $team->deleted_by = null;
        $team->save();

        return response()->json([
            'status' => true,
            'message' => 'Team restored successfully!',
        ]);
    }
}
