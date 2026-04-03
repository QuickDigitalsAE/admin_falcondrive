<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIs\AuthController;
use App\Http\Controllers\APIs\UserController;
use App\Http\Controllers\APIs\ManagerController;
use App\Http\Controllers\APIs\EmployeeController;
use App\Http\Controllers\APIs\ComplaintController;
use App\Http\Controllers\APIs\PublicHolidayController;
use App\Http\Controllers\APIs\NotificationController;
use App\Http\Controllers\APIs\KPIController;
use App\Http\Controllers\APIs\ShiftController;
use App\Http\Controllers\APIs\AttendanceController;
use App\Http\Controllers\APIs\LeaveRequestController;
use App\Http\Controllers\APIs\JobController;
use App\Http\Controllers\APIs\DepartmentController;
use App\Http\Controllers\APIs\PermissionController;
use App\Http\Controllers\APIs\OnBoardingController;
use App\Http\Controllers\APIs\AttachmentController;
use App\Http\Controllers\APIs\ContractController;
use App\Http\Controllers\APIs\TeamController;
use App\Http\Controllers\APIs\RoleController;
use App\Http\Controllers\APIs\PasswordResetController;
use App\Http\Controllers\APIs\MediaUploadController;
use App\Http\Controllers\APIs\UserActivityLogController;
use App\Http\Controllers\APIs\SettingController;
use App\Http\Controllers\APIs\RequestController;
use App\Http\Controllers\APIs\ResignationController;
use App\Http\Controllers\APIs\PayrollController;
use App\Http\Controllers\APIs\EventController;
use App\Http\Controllers\APIs\searchController;
use App\Http\Controllers\APIs\ContactController;

// User login api
Route::post('/login', [AuthController::class, 'login']);

// Define your registration API route
Route::post('/register', [AuthController::class, 'register'])->name('api.register');


Route::post('/forget-password', [PasswordResetController::class, 'forgetPassword']);
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    
    // User logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // routes grouping
    Route::group(['prefix' => 'users'], function() { 

        // Add user
        Route::post('/', [UserController::class, 'postUser']);

        // Get all users
        Route::get('/', [UserController::class, 'getUsers']);

        // Get my users
        Route::get('/my', [UserController::class, 'getMyUsers']);
        
        // Get Detail
        Route::get('/{id}', [UserController::class, 'editUser']);

        // Update a user by ID
        Route::put('/{id}', [UserController::class, 'updateUser']);

        // Change password of a user
        Route::put('/change-password/{id}', [UserController::class, 'changePassword']);

        // Delete a user by ID
        Route::delete('/{id}', [UserController::class, 'deleteUser']);

        // revoke a user by ID
        Route::put('/revoke/{id}', [UserController::class, 'revokeUser']);

    });

    Route::group(['prefix' => 'manager'], function() { 

        // Add manager
        Route::post('/', [ManagerController::class, 'postManager']);

        // Get all Managers
        Route::get('/', [ManagerController::class, 'getManagers']);

        // Get my Managers
        Route::get('/my', [ManagerController::class, 'getMyManagers']);
        
        // Get Detail
        Route::get('/{id}', [ManagerController::class, 'editManager']);

        // Update a Manager by ID
        Route::put('/{id}', [ManagerController::class, 'updateManager']);

        // Change password of a Manager
        Route::put('/change-password/{id}', [ManagerController::class, 'changePassword']);

        // Delete a Manager by ID
        Route::delete('/{id}', [ManagerController::class, 'deleteManager']);

        // revoke a Manager by ID
        Route::put('/revoke/{id}', [ManagerController::class, 'revokeManager']);

    });

    Route::group(['prefix' => 'employees'], function() { 

        // Add Employee
        Route::post('/', [EmployeeController::class, 'postEmployee']);

        // Get all Employee
        Route::get('/', [EmployeeController::class, 'getEmployees']);

        // Get my Employees
        Route::get('/my', [EmployeeController::class, 'getMyEmployees']);
        
        // Get Detail
        Route::get('/{id}', [EmployeeController::class, 'editEmployee']);

        // Update a Employee by ID
        Route::put('/{id}', [EmployeeController::class, 'updateEmployee']);

        // Change password of a Employee
        Route::put('/change-password/{id}', [EmployeeController::class, 'changePassword']);

        // Delete a Employee by ID
        Route::delete('/{id}', [EmployeeController::class, 'deleteEmployee']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [EmployeeController::class, 'revokeEmployee']);

    });

    Route::group(['prefix' => 'complaints'], function() { 

        // Add complaints
        Route::post('/', [ComplaintController::class, 'postComplaint']);

        // Get all complaints
        Route::get('/', [ComplaintController::class, 'getComplaints']);

        // Get my complaints
        Route::get('/my', [ComplaintController::class, 'getMyComplaints']);
        
        // Get Detail
        Route::get('/{id}', [ComplaintController::class, 'editComplaint']);

        // Update a complaints by ID
        Route::put('/{id}', [ComplaintController::class, 'updateComplaint']);

        // Delete a complaints by ID
        Route::delete('/{id}', [ComplaintController::class, 'deleteComplaint']);

        // revoke a complaints by ID
        Route::put('/revoke/{id}', [ComplaintController::class, 'revokeComplaint']);

    });

    Route::group(['prefix' => 'public-holidays'], function() {

        // Add public holiday
        Route::post('/', [PublicHolidayController::class, 'postPublicHoliday']);

        // Get all public holidays
        Route::get('/', [PublicHolidayController::class, 'getPublicHolidays']);

        // Get my public holidays
        Route::get('/my', [PublicHolidayController::class, 'getMyPublicHolidays']);

        // Get detail of a public holiday
        Route::get('/{id}', [PublicHolidayController::class, 'editPublicHoliday']);

        // Update a public holiday by ID
        Route::put('/{id}', [PublicHolidayController::class, 'updatePublicHoliday']);

        // Delete a public holiday by ID
        Route::delete('/{id}', [PublicHolidayController::class, 'deletePublicHoliday']);

        // Revoke (restore) a public holiday by ID
        Route::put('/revoke/{id}', [PublicHolidayController::class, 'revokePublicHoliday']);

    });

    Route::group(['prefix' => 'notifications'], function() {

        // Add a new notification
        Route::post('/', [NotificationController::class, 'postNotification']);

        // Get all notifications
        Route::get('/', [NotificationController::class, 'getNotifications']);

        // Get my notifications
        Route::get('/my', [NotificationController::class, 'getMyNotifications']);

        // Get detail of a notification
        Route::get('/{id}', [NotificationController::class, 'editNotification']);

        // Update a notification by ID
        Route::put('/{id}', [NotificationController::class, 'updateNotification']);

        // Delete a notification by ID
        Route::delete('/{id}', [NotificationController::class, 'deleteNotification']);

        // Revoke (restore) a deleted notification by ID
        Route::put('/revoke/{id}', [NotificationController::class, 'revokeNotification']);

    });

    Route::group(['prefix' => 'kpi'], function() { 

        // Add kpi
        Route::post('/', [KPIController::class, 'postKpi']);

        // Get all kpi
        Route::get('/', [KPIController::class, 'getKpis']);

        // Get my kpi
        Route::get('/my', [KPIController::class, 'getMyKpis']);
        
        // Get Detail
        Route::get('/{id}', [KPIController::class, 'editKpi']);

        // Update a kpi by ID
        Route::put('/{id}', [KPIController::class, 'updateKpi']);

        // Delete a kpi by ID
        Route::delete('/{id}', [KPIController::class, 'deleteKpi']);

        // revoke a kpi by ID
        Route::put('/revoke/{id}', [KPIController::class, 'revokeKpi']);

    });

    Route::group(['prefix' => 'job'], function() { 

        // Add Employee
        Route::post('/', [JobController::class, 'postJob']);

        // Get all Employee
        Route::get('/', [JobController::class, 'getJobs']);

        // Get my Employees
        Route::get('/my', [JobController::class, 'getMyJobs']);
        
        // Get Detail
        Route::get('/{id}', [JobController::class, 'editJob']);

        // Update a Employee by ID
        Route::put('/{id}', [JobController::class, 'updateJob']);

        // Delete a Employee by ID
        Route::delete('/{id}', [JobController::class, 'deleteJob']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [JobController::class, 'revokeJob']);

    });

    Route::group(['prefix' => 'department'], function() { 

        // Add Employee
        Route::post('/', [DepartmentController::class, 'postDepartment']);

        // Get all Employee
        Route::get('/', [DepartmentController::class, 'getDepartments']);

        // Get my Employees
        Route::get('/my', [DepartmentController::class, 'getMyDepartments']);
        
        // Get Detail
        Route::get('/{id}', [DepartmentController::class, 'editDepartment']);

        // Update a Employee by ID
        Route::put('/{id}', [DepartmentController::class, 'updateDepartment']);

        // Delete a Employee by ID
        Route::delete('/{id}', [DepartmentController::class, 'deleteDepartment']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [DepartmentController::class, 'revokeDepartment']);

    });

    Route::group(['prefix' => 'onboarding'], function() { 

        // Add Employee
        Route::post('/', [OnBoardingController::class, 'postOnBoarding']);

        // Get all Employee
        Route::get('/', [OnBoardingController::class, 'getOnBoardings']);

        // Get my Employees
        Route::get('/my', [OnBoardingController::class, 'getMyOnBoardings']);
        
        // Get Detail
        Route::get('/{id}', [OnBoardingController::class, 'editOnBoarding']);

        // Update a Employee by ID
        Route::put('/{id}', [OnBoardingController::class, 'updateOnBoarding']);

        // Delete a Employee by ID
        Route::delete('/{id}', [OnBoardingController::class, 'deleteOnBoarding']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [OnBoardingController::class, 'revokeOnBoarding']);

    });

    Route::group(['prefix' => 'attachment'], function() { 

        // Add Employee
        Route::post('/', [AttachmentController::class, 'postAttachment']);

        // Get all Employee
        Route::get('/', [AttachmentController::class, 'getAttachments']);

        // Get my Employees
        Route::get('/my', [AttachmentController::class, 'getMyAttachments']);
        
        // Get Detail
        Route::get('/{id}', [AttachmentController::class, 'editAttachment']);

        // Update a Employee by ID
        Route::put('/{id}', [AttachmentController::class, 'updateAttachment']);

        // Delete a Employee by ID
        Route::delete('/{id}', [AttachmentController::class, 'deleteAttachment']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [AttachmentController::class, 'revokeAttachment']);

    });

    Route::group(['prefix' => 'contract'], function() { 

        // Add Employee
        Route::post('/', [ContractController::class, 'postContract']);

        // Get all Employee
        Route::get('/', [ContractController::class, 'getContracts']);

        // Get my Employees
        Route::get('/my', [ContractController::class, 'getMyContracts']);
        
        // Get Detail
        Route::get('/{id}', [ContractController::class, 'editContract']);

        // Update a Employee by ID
        Route::put('/{id}', [ContractController::class, 'updateContract']);

        // Delete a Employee by ID
        Route::delete('/{id}', [ContractController::class, 'deleteContract']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [ContractController::class, 'revokeContract']);

    });

    Route::group(['prefix' => 'team'], function() { 

        // Add Employee
        Route::post('/', [TeamController::class, 'postTeam']);

        // Get all Employee
        Route::get('/', [TeamController::class, 'getTeams']);

        // Get my Employees
        Route::get('/my', [TeamController::class, 'getMyTeams']);
        
        // Get Detail
        Route::get('/{id}', [TeamController::class, 'editTeam']);

        // Update a Employee by ID
        Route::put('/{id}', [TeamController::class, 'updateTeam']);

        // Delete a Employee by ID
        Route::delete('/{id}', [TeamController::class, 'deleteTeam']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [TeamController::class, 'revokeTeam']);

    });

    Route::group(['prefix' => 'shift'], function() { 

        // Add Employee
        Route::post('/', [ShiftController::class, 'postShift']);

        // Get all Employee
        Route::get('/', [ShiftController::class, 'getShifts']);

        // Get my Employees
        Route::get('/my', [ShiftController::class, 'getMyShifts']);
        
        // Get Detail
        Route::get('/{id}', [ShiftController::class, 'editShift']);

        // Update a Employee by ID
        Route::put('/{id}', [ShiftController::class, 'updateShift']);

        // Delete a Employee by ID
        Route::delete('/{id}', [ShiftController::class, 'deleteShift']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [ShiftController::class, 'revokeShift']);

    });

    Route::group(['prefix' => 'attendance'], function () {

        
        // Sheet Attendance
        Route::get('/sheet', [AttendanceController::class, 'sheetAttendance']);
        
        // Create Attendance Entry
        Route::post('/', [AttendanceController::class, 'postAttendance']);

        // Get All Attendance Entries
        Route::get('/', [AttendanceController::class, 'getAttendances']);

        // Get My Attendance Entries
        Route::get('/my', [AttendanceController::class, 'getMyAttendances']);

        // Get Attendance Detail
        Route::get('/{id}', [AttendanceController::class, 'editAttendance']);

        // Update Attendance
        Route::put('/{id}', [AttendanceController::class, 'updateAttendance']);

        // Soft Delete Attendance
        Route::delete('/{id}', [AttendanceController::class, 'deleteAttendance']);

        // Revoke Deleted Attendance
        Route::put('/revoke/{id}', [AttendanceController::class, 'revokeAttendance']);

    });

    Route::group(['prefix' => 'leave-requests'], function () {

        // Create Leave Request
        Route::post('/', [LeaveRequestController::class, 'postLeaveRequest']);

        // Get All Leave Requests
        Route::get('/', [LeaveRequestController::class, 'getLeaveRequests']);

        // Get My Leave Requests
        Route::get('/my', [LeaveRequestController::class, 'getMyLeaveRequests']);

        // Get Single Leave Request Detail
        Route::get('/{id}', [LeaveRequestController::class, 'editLeaveRequest']);

        // Update Leave Request
        Route::put('/{id}', [LeaveRequestController::class, 'updateLeaveRequest']);

        // Soft Delete Leave Request
        Route::delete('/{id}', [LeaveRequestController::class, 'deleteLeaveRequest']);

        // Restore Deleted Leave Request
        Route::put('/revoke/{id}', [LeaveRequestController::class, 'revokeLeaveRequest']);
    });

    Route::group(['prefix' => 'requests'], function () {

        // Create Request
        Route::post('/', [RequestController::class, 'postRequest']);

        // Get All Requests
        Route::get('/', [RequestController::class, 'getRequests']);

        // Get My Requests
        Route::get('/my', [RequestController::class, 'getMyRequests']);

        // Get Request Detail
        Route::get('/{id}', [RequestController::class, 'editRequest']);

        // Update Request
        Route::put('/{id}', [RequestController::class, 'updateRequest']);

        // Soft Delete Request
        Route::delete('/{id}', [RequestController::class, 'deleteRequest']);

        // Revoke Deleted Request
        Route::put('/revoke/{id}', [RequestController::class, 'revokeRequest']);
    });

    Route::group(['prefix' => 'resignations'], function () {

        // Create Resignation
        Route::post('/', [ResignationController::class, 'postResignation']);

        // Get All Resignations
        Route::get('/', [ResignationController::class, 'getResignations']);

        // Get My Resignations
        Route::get('/my', [ResignationController::class, 'getMyResignations']);

        // Get Resignation Detail
        Route::get('/{id}', [ResignationController::class, 'editResignation']);

        // Update Resignation
        Route::put('/{id}', [ResignationController::class, 'updateResignation']);

        // Soft Delete Resignation
        Route::delete('/{id}', [ResignationController::class, 'deleteResignation']);

        // Revoke Deleted Resignation
        Route::put('/revoke/{id}', [ResignationController::class, 'revokeResignation']);
    });

    Route::group(['prefix' => 'payrolls'], function () {

        // Create Payroll
        Route::post('/', [PayrollController::class, 'postPayroll']);

        // Get All Payrolls
        Route::get('/', [PayrollController::class, 'getPayrolls']);

        // Get My Payrolls
        Route::get('/my', [PayrollController::class, 'getMyPayrolls']);

        // Get Payroll Detail
        Route::get('/{id}', [PayrollController::class, 'editPayroll']);

        // Update Payroll
        Route::put('/{id}', [PayrollController::class, 'updatePayroll']);

        // Soft Delete Payroll
        Route::delete('/{id}', [PayrollController::class, 'deletePayroll']);

        // Revoke Deleted Payroll
        Route::put('/revoke/{id}', [PayrollController::class, 'revokePayroll']);
    });

    Route::group(['prefix' => 'activities'], function(){
        
        // Get all user activity logs on the admin side
        Route::get('/adminSideAllLogs/{per_page_count?}', [UserActivityLogController::class, 'adminSideAllLogs']);
        
        // Get all user auth activities logs
        Route::get('/authHistory/{per_page_count?}', [UserActivityLogController::class, 'userAuthHistory']);
    });

    Route::group(['prefix' => 'permissions'], function() {

        // Create Permissions
        Route::post('/', [PermissionController::class, 'postPermission']);

        // Get all permissions
        Route::get('/', [PermissionController::class, 'getPermissions']);

        // Get all table name
        Route::get('/table-list', [PermissionController::class, 'getAllTables']);

        // Update
        Route::put('/{id}', [PermissionController::class, 'updatePermission']);
        
        //Delete Group
        Route::delete('/{group?}', [PermissionController::class, 'deletePermissions']);

        // revoke
        Route::put('/revoke/{group}', [PermissionController::class, 'revokePermissions']);
    });

    Route::group(['prefix' => 'roles'], function() {  
        
        // Create a role
        Route::post('/', [RoleController::class, 'postRole']);

        // Get all roles
        Route::get('/', [RoleController::class, 'getRoles']);

        // Get a specific role with permissions by role ID
        Route::get('/{id}', [RoleController::class, 'editRole']);

        // Update a specific role with permissions by role ID
        Route::put('/{id}', [RoleController::class, 'updateRole']);

        //Delete role with permissions by role ID
        Route::delete('/{id}', [RoleController::class, 'deleteRole']);
        
        // revoke
        Route::put('/revoke/{id}', [RoleController::class, 'revokeRole']);
    });

    Route::group(['prefix' => 'media'], function() {

        // Get all images
        Route::get('/', [MediaUploadController::class, 'getAllFiles']);

        // Get all folder list
        Route::get('/folder-list', [MediaUploadController::class, 'getAllFolders']);

        Route::post('/upload', [MediaUploadController::class, 'uploadImage']);

        //Delete role with permissions by role ID
        Route::delete('/', [MediaUploadController::class, 'deleteFile']);
    });

    Route::group(['prefix' => 'event'], function () {

        // Create new event
        Route::post('/', [EventController::class, 'postEvent']);

        // Get all events
        Route::get('/', [EventController::class, 'getEvents']);

        // Get my events
        Route::get('/my', [EventController::class, 'getMyEvents']);

        // Get event detail
        Route::get('/{id}', [EventController::class, 'editEvent']);

        // Update an event by ID
        Route::put('/{id}', [EventController::class, 'updateEvent']);

        // Delete an event by ID (soft delete)
        Route::delete('/{id}', [EventController::class, 'deleteEvent']);

        // Restore a soft-deleted event
        Route::put('/revoke/{id}', [EventController::class, 'revokeEvent']);

    });

    Route::group(['prefix' => 'setting'], function() {

        // Update a Employee by ID
        Route::put('/update-profile', [SettingController::class, 'updateProfile']);

        // Get login user profile
        Route::get('/my-profile', [SettingController::class, 'getMyProfile']);

        // Update login user password
        Route::put('/change-password', [SettingController::class, 'myChangePassword']);

    });

    Route::group(['prefix' => 'search'], function() {  
        // Get all record
        Route::get('/', [searchController::class, 'getAll']);
    });

    Route::group(['prefix' => 'contact'], function() {  
        // Get all record
        Route::get('/', [ContactController::class, 'getAll']);
    });
});