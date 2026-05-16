<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Job;

class searchController extends Controller
{
    
    public function getAll(Request $request)
    {
        $keyword   = $request->query('keyword');
        $tableName = $request->query('table');

        if (!$keyword) {
            return response()->json([
                'status'  => false,
                'message' => 'keyword parameter is required.',
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized user.',
            ], 401);
        }

        $database = env('DB_DATABASE');
        $tables   = [];

        if ($user->id == 1) {
            if ($tableName) {
                if (!Schema::hasTable($tableName)) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Table '$tableName' does not exist.",
                    ], 404);
                }
                $tables = [$tableName];
            } else {
                $tableResults = DB::select('SHOW TABLES');
                foreach ($tableResults as $tableObj) {
                    $tables[] = $tableObj->{"Tables_in_{$database}"};
                }
            }
        } else {
            $permittedTables = $user->getAllPermissions()
                ->whereNotNull('table_name')
                ->pluck('table_name')
                ->unique()
                ->values()
                ->toArray();

            if ($tableName) {
                if (!in_array($tableName, $permittedTables)) {
                    return response()->json([
                        'status'  => false,
                        'message' => "You do not have permission to search in '$tableName'.",
                    ], 403);
                }
                $tables = [$tableName];
            } else {
                $tables = $permittedTables;
            }
        }

        $result = [];

        foreach ($tables as $table) {
            if (in_array($table, ['migrations', 'user_activity_logs', 'password_resets', 'failed_jobs', 'personal_access_tokens'])) {
                continue;
            }

            $columns = Schema::getColumnListing($table);

            $matches = DB::table($table)
                ->where(function($q) use ($columns, $keyword) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$keyword}%");
                    }
                })
                ->get();

            // Handle user roles if table is 'users'
            if ($table === 'users' && $matches->isNotEmpty()) {
                foreach ($matches as $match) {
                    $roles = DB::table('model_has_roles')
                        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->where('model_has_roles.model_id', $match->id)
                        ->pluck('roles.name');

                    foreach ($roles as $role) {
                        if (!isset($result[$role])) {
                            $result[$role] = [];
                        }
                        $result[$role][] = $match;
                    }
                }
            } elseif ($table === 'permissions' && $matches->isNotEmpty()) {
                $withGroup = $matches->map(function ($permission) {
                    $permission->group = Str::before($permission->name, '_');
                    return $permission;
                });

                $result[$table] = $withGroup;
            } elseif ($matches->isNotEmpty()) {
                $enhanced = $matches->map(function ($row) use ($table) {
                    $row = (array) $row;

                    // Replace user_id with user_name
                    if (isset($row['user_id'])) {
                        $row['user_name'] = optional(User::find($row['user_id']))->name ?? '';
                    } 

                    if (isset($row['manager_id'])) {
                        $row['manager_name'] = optional(User::find($row['manager_id']))->name ?? '';
                    } 

                    if (isset($row['approver_user_id'])) {
                        $row['approver_name'] = optional(User::find($row['approver_user_id']))->name ?? '';
                    } 

                    if (isset($row['assigned_to'])) {
                        $row['assigned_to_name'] = optional(User::find($row['assigned_to']))->name ?? '';
                    }

                    if (isset($row['it_approver_user_id'])) {
                        $row['it_approver_name'] = optional(User::find($row['it_approver_user_id']))->name ?? '';
                    }
                    
                    if (isset($row['interview_1_by'])) {
                        $row['interview_1_by_name'] = optional(User::find($row['interview_1_by']))->name ?? '';
                    }
                    
                    if (isset($row['interview_2_by'])) {
                        $row['interview_2_by_name'] = optional(User::find($row['interview_2_by']))->name ?? '';
                    }
                    
                    if (isset($row['interview_3_by'])) {
                        $row['interview_3_by_name'] = optional(User::find($row['interview_3_by']))->name ?? '';
                    }

                    if (isset($row['job_id'])) {
                        $row['job_title'] = optional(Job::find($row['job_id']))->title ?? '';
                    }

                    // Convert created_by, updated_by, deleted_by
                    foreach (['created_by', 'updated_by', 'deleted_by'] as $field) {
                        if (isset($row[$field])) {
                            $row[$field] = optional(User::find($row[$field]))->name ?? '';
                        }
                    }

                    return $row;
                });

                $result[$table] = $enhanced;
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Search results fetched successfully!',
            'data'    => $result,
        ]);
    }
}
