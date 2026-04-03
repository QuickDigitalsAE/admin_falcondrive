<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OnBoarding;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUrlTrait;

class OnboardingController extends Controller
{
    use ImageUrlTrait;

    public function __construct()
    {
        $this->middleware('permission:Onboarding_ViewAll', ['only' => ['getOnboardings']]);
        $this->middleware('permission:Onboarding_ViewMine', ['only' => ['getMyOnboardings']]);
        $this->middleware('permission:Onboarding_View', ['only' => ['editOnboarding']]);
        $this->middleware('permission:Onboarding_Add', ['only' => ['postOnboarding']]);
        $this->middleware('permission:Onboarding_Edit', ['only' => ['updateOnboarding']]);
        $this->middleware('permission:Onboarding_Delete', ['only' => ['deleteOnboarding']]);
        $this->middleware('permission:Onboarding_Revoke', ['only' => ['revokeOnboarding']]);
    }

    public function getOnboardings(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $user_id = $request->query('user_id');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');

        // Base query
        $query = $isDeleted ? OnBoarding::onlyTrashed() : OnBoarding::query();
        $query->with([
            'user:id,name',
            'job:id,title',
            'interview1By:id,name',
            'interview2By:id,name',
            'interview3By:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])->orderBy('created_at', 'DESC');

        // Search filter
        if (!empty($search)) {
            $query->where('offer_status', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
        }

        // Filter by user_id
        if (!empty($user_id)) {
            $query->where('user_id', $user_id);
        }

        // Formatter
        $format = function ($item) {
            return [
                'id' => $item->id,
                'job_id' => $item->job_id,
                'job_title' => optional($item->job)->title ?? '',
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'is_cv_collected' => $item->is_cv_collected,

                'interview_1_by' => $item->interview_1_by,
                'interview_1_by_name' => optional($item->interview1By)->name ?? '',
                'interview_1_feedback' => $item->interview_1_feedback,
                'interview_1_status' => $item->interview_1_status,
                'interview_1_datetime' => $item->interview_1_datetime,

                'interview_2_by' => $item->interview_2_by,
                'interview_2_by_name' => optional($item->interview2By)->name ?? '',
                'interview_2_feedback' => $item->interview_2_feedback,
                'interview_2_status' => $item->interview_2_status,
                'interview_2_datetime' => $item->interview_2_datetime,

                'interview_3_by' => $item->interview_3_by,
                'interview_3_by_name' => optional($item->interview3By)->name ?? '',
                'interview_3_feedback' => $item->interview_3_feedback,
                'interview_3_status' => $item->interview_3_status,
                'interview_3_datetime' => $item->interview_3_datetime,

                'offer_status' => $item->offer_status,
                'offer_amount' => $item->offer_amount,

                'created_by_user' => optional($item->createdByUser)->name ?? '',
                'updated_by_user' => optional($item->updatedByUser)->name ?? '',
                'deleted_by_user' => optional($item->deletedByUser)->name ?? '',

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        // Export
        if ($isExport) {
            $records = $query->get();

            $csvHeader = [
                'ID', 'User', 'Job Title', 'CV Collected', 
                'Interview 1 By','Interview 1 Status', 'Interview 1 Feedback', 'Interview 1 Datetime',
                'Interview 2 By','Interview 2 Status', 'Interview 2 Feedback', 'Interview 2 Datetime',
                'Interview 3 By','Interview 3 Status', 'Interview 3 Feedback', 'Interview 3 Datetime',
                'Offer Status', 'Offer Amount',
                'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $item) {
                    $formatted = $format($item);

                    $row = [
                        $formatted['id'],
                        $formatted['user_name'],
                        $formatted['job_title'],
                        $formatted['is_cv_collected'] ? 'Yes' : 'No',

                        $formatted['interview_1_by_name'],
                        $formatted['interview_1_status'],
                        $formatted['interview_1_feedback'],
                        $formatted['interview_1_datetime'],

                        $formatted['interview_2_by_name'],
                        $formatted['interview_2_status'],
                        $formatted['interview_2_feedback'],
                        $formatted['interview_2_datetime'],

                        $formatted['interview_3_by_name'],
                        $formatted['interview_3_status'],
                        $formatted['interview_3_feedback'],
                        $formatted['interview_3_datetime'],

                        $formatted['offer_status'],
                        $formatted['offer_amount'],

                        $formatted['created_by_user'],
                        $formatted['updated_by_user'],
                        $formatted['created_at'],
                        $formatted['updated_at'],
                    ];

                    if ($isDeleted) {
                        $row[] = $formatted['deleted_by_user'];
                        $row[] = $formatted['deleted_at'];
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=onboarding_export_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        // Paginated response
        $onboardings = $query->paginate($per_page);
        $formattedList = $onboardings->map($format);

        return response()->json([
            'status' => true,
            'message' => 'Onboarding list fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $onboardings->total(),
                    'per_page' => $onboardings->perPage(),
                    'current_page' => $onboardings->currentPage(),
                    'last_page' => $onboardings->lastPage(),
                ],
            ],
        ]);
    }

    public function getMyOnboardings(Request $request)
    {
        $per_page = getPerPage();
        $search = $request->query('search');
        $isDeleted = $request->query('is_deleted');
        $isExport = $request->query('is_export');
        $auth_id = auth()->id();

        // Base query (with soft delete support)
        $query = $isDeleted ? OnBoarding::onlyTrashed() : OnBoarding::query();

        $query->with([
            'user:id,name',
            'job:id,title',
            'interview1By:id,name',
            'interview2By:id,name',
            'interview3By:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
        ])
        ->where('created_by', $auth_id)
        ->orderBy('created_at', 'DESC');

        // Search filter
        if (!empty($search)) {
            $query->where('offer_status', 'LIKE', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
        }

        // Formatter closure
        $format = function ($item) {
            return [
                'id' => $item->id,
                'job_id' => $item->job_id,
                'job_title' => optional($item->job)->title ?? '',
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'is_cv_collected' => $item->is_cv_collected,

                'interview_1_by' => $item->interview_1_by,
                'interview_1_by_name' => optional($item->interview1By)->name ?? '',
                'interview_1_feedback' => $item->interview_1_feedback,
                'interview_1_status' => $item->interview_1_status,
                'interview_1_datetime' => $item->interview_1_datetime,

                'interview_2_by' => $item->interview_2_by,
                'interview_2_by_name' => optional($item->interview2By)->name ?? '',
                'interview_2_feedback' => $item->interview_2_feedback,
                'interview_2_status' => $item->interview_2_status,
                'interview_2_datetime' => $item->interview_2_datetime,

                'interview_3_by' => $item->interview_3_by,
                'interview_3_by_name' => optional($item->interview3By)->name ?? '',
                'interview_3_feedback' => $item->interview_3_feedback,
                'interview_3_status' => $item->interview_3_status,
                'interview_3_datetime' => $item->interview_3_datetime,

                'offer_status' => $item->offer_status,
                'offer_amount' => number_format((float)$item->offer_amount, 2, '.', ''),

                'created_by_user' => optional($item->createdByUser)->name ?? '',
                'updated_by_user' => optional($item->updatedByUser)->name ?? '',
                'deleted_by_user' => optional($item->deletedByUser)->name ?? '',

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        // Export logic
        if ($isExport) {
            $records = $query->get();

            $csvHeader = [
                'ID', 'User', 'Job Title', 'CV Collected',
                'Interview 1 By','Interview 1 Status', 'Interview 1 Feedback', 'Interview 1 Datetime',
                'Interview 2 By','Interview 2 Status', 'Interview 2 Feedback', 'Interview 2 Datetime',
                'Interview 3 By','Interview 3 Status', 'Interview 3 Feedback', 'Interview 3 Datetime',
                'Offer Status', 'Offer Amount',
                'Created By', 'Updated By', 'Created At', 'Updated At'
            ];

            if ($isDeleted) {
                $csvHeader[] = 'Deleted By';
                $csvHeader[] = 'Deleted At';
            }

            $callback = function () use ($records, $csvHeader, $isDeleted, $format) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $csvHeader);

                foreach ($records as $item) {
                    $formatted = $format($item);

                    $row = [
                        $formatted['id'],
                        $formatted['user_name'],
                        $formatted['job_title'],
                        $formatted['is_cv_collected'] ? 'Yes' : 'No',

                        $formatted['interview_1_by_name'],
                        $formatted['interview_1_status'],
                        $formatted['interview_1_feedback'],
                        $formatted['interview_1_datetime'],

                        $formatted['interview_2_by_name'],
                        $formatted['interview_2_status'],
                        $formatted['interview_2_feedback'],
                        $formatted['interview_2_datetime'],

                        $formatted['interview_3_by_name'],
                        $formatted['interview_3_status'],
                        $formatted['interview_3_feedback'],
                        $formatted['interview_3_datetime'],

                        $formatted['offer_status'],
                        $formatted['offer_amount'],

                        $formatted['created_by_user'],
                        $formatted['updated_by_user'],
                        $formatted['created_at'],
                        $formatted['updated_at'],
                    ];

                    if ($isDeleted) {
                        $row[] = $formatted['deleted_by_user'];
                        $row[] = $formatted['deleted_at'];
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=my_onboardings_' . now()->format('Ymd_His') . '.csv',
            ]);
        }

        // Paginated response
        $onboardings = $query->paginate($per_page);
        $formattedList = $onboardings->map($format);

        return response()->json([
            'status' => true,
            'message' => 'My onboardings fetched successfully!',
            'data' => [
                'list' => $formattedList,
                'pagination' => [
                    'total' => $onboardings->total(),
                    'per_page' => $onboardings->perPage(),
                    'current_page' => $onboardings->currentPage(),
                    'last_page' => $onboardings->lastPage(),
                ],
            ],
        ]);
    }

    public function postOnboarding(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer',
            'user_id' => 'required|integer',
            'is_cv_collected' => 'nullable|boolean',
            'offer_status' => 'nullable|string|max:255',
            'offer_amount' => 'nullable|numeric',
            'documents_from_candidate' => 'nullable|array',
            'documents_from_company' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $onboarding = Onboarding::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Onboarding created successfully!',
            'data' => $onboarding,
        ], Response::HTTP_CREATED);
    }

    public function editOnboarding($id)
    {
        $onboarding = OnBoarding::with([
            'user:id,name',
            'job:id,title',
            'interview1By:id,name',
            'interview2By:id,name',
            'interview3By:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
            'deletedByUser:id,name',
            'attachments:id,table_primary_key,table_name,type,category,title,desc,url' // Attachments
        ])->findOrFail($id);

        $format = function ($item) {
            return [
                'id' => $item->id,
                'job_id' => $item->job_id,
                'job_title' => optional($item->job)->title ?? '',
                'user_id' => $item->user_id,
                'user_name' => optional($item->user)->name ?? '',
                'is_cv_collected' => $item->is_cv_collected,

                'interview_1_by' => $item->interview_1_by,
                'interview_1_by_name' => optional($item->interview1By)->name ?? '',
                'interview_1_feedback' => $item->interview_1_feedback,
                'interview_1_status' => $item->interview_1_status,
                'interview_1_datetime' => $item->interview_1_datetime,

                'interview_2_by' => $item->interview_2_by,
                'interview_2_by_name' => optional($item->interview2By)->name ?? '',
                'interview_2_feedback' => $item->interview_2_feedback,
                'interview_2_status' => $item->interview_2_status,
                'interview_2_datetime' => $item->interview_2_datetime,

                'interview_3_by' => $item->interview_3_by,
                'interview_3_by_name' => optional($item->interview3By)->name ?? '',
                'interview_3_feedback' => $item->interview_3_feedback,
                'interview_3_status' => $item->interview_3_status,
                'interview_3_datetime' => $item->interview_3_datetime,

                'offer_status' => $item->offer_status,
                'offer_amount' => $item->offer_amount,

                'created_by_user' => optional($item->createdByUser)->name ?? '',
                'updated_by_user' => optional($item->updatedByUser)->name ?? '',
                'deleted_by_user' => optional($item->deletedByUser)->name ?? '',

                'attachments' => collect($item->attachments)->map(function ($att) {
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

                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        };

        return response()->json([
            'status' => true,
            'message' => 'Onboarding detail fetched successfully!',
            'data' => $format($onboarding),
        ]);
    }

    public function updateOnboarding(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $request->validate([
            'job_id' => 'required|integer',
            'user_id' => 'required|integer',
            'is_cv_collected' => 'nullable|boolean',
            'offer_status' => 'nullable|string|max:255',
            'offer_amount' => 'nullable|numeric',
            'documents_from_candidate' => 'nullable|array',
            'documents_from_company' => 'nullable|array',
        ]);

        $data = $request->all();
        $data['updated_by'] = Auth::id();

        $onboarding->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Onboarding updated successfully!',
            'data' => $onboarding,
        ]);
    }

    public function deleteOnboarding($id)
    {
        $onboarding = Onboarding::findOrFail($id);
        $onboarding->update(['deleted_by' => Auth::id()]);
        $onboarding->delete();

        return response()->json([
            'status' => true,
            'message' => 'Onboarding deleted successfully!',
        ]);
    }

    public function revokeOnboarding($id)
    {
        $onboarding = Onboarding::onlyTrashed()->findOrFail($id);
        $onboarding->restore();
        $onboarding->deleted_by = null;
        $onboarding->save();

        return response()->json([
            'status' => true,
            'message' => 'Onboarding restored successfully!',
        ]);
    }
}
