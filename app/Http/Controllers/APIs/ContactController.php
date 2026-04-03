<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ImageUrlTrait;

class ContactController extends Controller
{
    use ImageUrlTrait;

    public function getAll(Request $request)
    {
        $search = $request->query('search');
        $is_export = $request->query('is_export');

        // Base query
        $query = User::query();

        // Eager load roles
        $query->with('roles')->orderBy('created_at', 'DESC');

        // Search by name or email
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Always get all users (no pagination)
        $users = $query->get();

        // Format function
        $formatUser = function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image ? $this->getImageUrl($user->profile_image) : null,
                'created_at' => $user->created_at,
            ];
        };

        // Group users by role
        $admins = [];
        $managers = [];
        $employees = [];

        foreach ($users as $user) {
            $formatted = $formatUser($user);

            foreach ($user->roles as $role) {
                if ($role && $role->id == 2) {
                    $admins[] = $formatted;
                    break;
                } elseif ($role && $role->id == 3) {
                    $managers[] = $formatted;
                    break;
                } elseif ($role && $role->id == 4) {
                    $employees[] = $formatted;
                    break;
                }
            }
        }

        // Export to CSV
        if ($is_export) {
            $csvHeader = ['ID', 'Name', 'Email', 'Phone', 'Profile Image', 'Created At'];

            $callback = function () use ($admins, $managers, $employees, $csvHeader) {
                $file = fopen('php://output', 'w');

                fputcsv($file, ['Admins']);
                fputcsv($file, $csvHeader);
                foreach ($admins as $admin) {
                    fputcsv($file, [
                        $admin['id'], $admin['name'], $admin['email'], $admin['phone'], $admin['profile_image'], $admin['created_at']
                    ]);
                }

                fputcsv($file, []);
                fputcsv($file, ['Managers']);
                fputcsv($file, $csvHeader);
                foreach ($managers as $manager) {
                    fputcsv($file, [
                        $manager['id'], $manager['name'], $manager['email'], $manager['phone'], $manager['profile_image'], $manager['created_at']
                    ]);
                }

                fputcsv($file, []);
                fputcsv($file, ['Employees']);
                fputcsv($file, $csvHeader);
                foreach ($employees as $employee) {
                    fputcsv($file, [
                        $employee['id'], $employee['name'], $employee['email'], $employee['phone'], $employee['profile_image'], $employee['created_at']
                    ]);
                }

                fclose($file);
            };

            $fileName = 'contacts_export_' . now()->format('Ymd_His') . '.csv';

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$fileName}",
            ]);
        }

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No users found!',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => true,
            'message' => 'Contact list fetched successfully.',
            'data' => [
                'admins' => $admins,
                'managers' => $managers,
                'employees' => $employees,
            ],
        ], Response::HTTP_OK);
    }

}
